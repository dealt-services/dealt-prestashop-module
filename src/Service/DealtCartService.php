<?php

declare(strict_types=1);

namespace DealtModule\Service;

use Cart;
use Context;
use DealtModule;
use DealtModule\Entity\DealtCartProductRef;
use DealtModule\Entity\DealtOffer;
use DealtModule\Presenter\DealtOfferPresenter;
use DealtModule\Repository\DealtCartProductRefRepository;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Tools\Helpers;
use Exception;
use Product;

/**
 * Dealt Cart class allows interacting with
 * the prestashop cart and enforcing constraints
 * for Dealt virtual products
 */
final class DealtCartService
{
    /** @var DealtOfferRepository */
    private $offerRepository;

    /** @var DealtCartProductRefRepository */
    private $dealtCartRefRepository;

    /** @var DealtOfferPresenter */
    private $offerPresenter;

    /** @var DealtModule */
    private $module;

    /**
     * internal flag to avoid recursive loop on cart
     * sanitization triggering cascading cart updates
     *
     * @var bool
     */
    private $cartSanitized = false;

    /**
     * @param DealtOfferRepository $offerRepository
     * @param DealtCartProductRefRepository $dealtCartRefRepository
     * @param DealtOfferPresenter $offerPresenter
     */
    public function __construct(
        $offerRepository,
        $dealtCartRefRepository,
        $offerPresenter
    ) {
        $this->offerRepository = $offerRepository;
        $this->dealtCartRefRepository = $dealtCartRefRepository;
        $this->offerPresenter = $offerPresenter;
    }

    /**
     * Attaches a dealt product to a product
     * currently in the prestashop cart and syncs
     * their quantities
     *
     * @param string $dealtOfferId
     * @param int $productId
     * @param int $productAttributeId
     *
     * @return bool
     */
    public function addDealtOfferToCart($dealtOfferId, $productId, $productAttributeId)
    {
        /** @var DealtOffer|null */
        $offer = $this->offerRepository->findOneBy(['dealtOfferId' => $dealtOfferId]);
        if ($offer == null) {
            throw new Exception('Unknown Dealt offer id');
        }

        $cart = Context::getContext()->cart;
        $cartProduct = Helpers::getProductFromCart($cart, $productId, $productAttributeId);

        if ($cartProduct == null) {
            throw new Exception('Cannot attach dealt offer to a product which is not currently in the cart');
        }

        $this->dealtCartRefRepository->create($cart->id, $productId, $productAttributeId, $offer);

        /* this will trigger the dealt cart sanitization via PS hooks */
        $result = Context::getContext()->cart->updateQty(
            (int) $cartProduct['cart_quantity'],
            $offer->getDealtProductId(),
            null,
            false,
        );

        return $result;
    }

    public function detachDealtOffer($dealtOfferId, $productId, $productAttributeId)
    {
        $cart = Context::getContext()->cart;
        $cartProductsIndex = Helpers::indexCartProducts($cart);

        /** @var DealtOffer */
        $offer = $this->offerRepository->findOneBy(['dealtOfferId' => $dealtOfferId]);

        if ($offer == null) {
            throw new Exception('Unknown dealt offer id');
        }

        /** @var DealtCartProductRef|null */
        $dealtCartRef = $this->dealtCartRefRepository->findOneBy(['cartId' => $cart->id, 'productId' => $productId, 'productAttributeId' => $productAttributeId, 'offer' => $offer]);

        if ($dealtCartRef == null) {
            throw new Exception('Cannot detach an unattached offer');
        }

        $offerProductId = $offer->getDealtProductId();
        $productQuantity = intval($cartProductsIndex[$productId][$productAttributeId]['cart_quantity']);

        $this->dealtCartRefRepository->delete($dealtCartRef->getId());
        Context::getContext()->cart->updateQty($productQuantity, $offerProductId, null, false, 'down');

        return ['deleted' => true];
    }

    /**
     * Filters in place the presented cart data
     * adds dealt specific data to products attached to a dealt offer
     *
     * @param mixed $presentedCart
     *
     * @return void
     */
    public function sanitizeCartPresenter(&$presentedCart)
    {
        $cart = Context::getContext()->cart;

        /** @var DealtCartProductRef[] */
        $dealtCartRefs = $this->dealtCartRefRepository->findBy(['cartId' => $cart->id]);

        $dealtCartDealtProductIds = array_map(function (DealtCartProductRef $dealtCartRef) {
            return $dealtCartRef
                ->getOffer()
                ->getDealtProductId();
        }, $dealtCartRefs);

        $presentedCart['products'] = array_filter($presentedCart['products'], function ($presentedCartProduct) use ($dealtCartDealtProductIds) {
            return !in_array(
                $presentedCartProduct['id_product'],
                $dealtCartDealtProductIds
            );
        });

        foreach ($presentedCart['products'] as &$cartProduct) {
            foreach ($dealtCartRefs as $dealtCartRef) {
                if (
                    $dealtCartRef->getProductId() == $cartProduct['id_product'] &&
                    $dealtCartRef->getProductAttributeId() == $cartProduct['id_product_attribute']
                ) {
                    $offer = $dealtCartRef->getOffer();

                    $cartProduct['dealt'] = $this->offerPresenter->present(
                        $offer,
                        $cart,
                        $cartProduct['id_product'],
                        $cartProduct['id_product_attribute'],
                    );
                }
            }
        }

        /**
         * sanitize total counts now that we
         * have filtered the dealt products
         */
        $totalCount = 0;

        foreach ($presentedCart['products'] as $product) {
            $totalCount += (int) $product['quantity'];
        }

        $presentedCart['products_count'] = $totalCount;

        /* sanitize subtotals for cart summary */
        $this->sanitizeSubTotals($cart, $presentedCart, $totalCount);
    }

    /**
     * Sanitization of prestashop cart against dealt constraints
     * - get all dealt cart products
     *
     * @param Cart $cart
     *
     * @return void
     */
    public function sanitizeDealtCart($cart)
    {
        if ($this->cartSanitized) {
            return;
        }

        $this->cartSanitized = true;
        $offers = $this->offerRepository->getDealtOffersFromCart($cart);
        $cartProductsIndex = Helpers::indexCartProducts($cart);

        /*
                 * If we have dealt offers present in the cart
                 * we need to ensure their quantities match their
                 * attached products
                 */
        foreach ($offers as $offer) {
            $quantity = 0;

            /** @var DealtCartProductRef[] */
            $dealtCartRefs = $this->dealtCartRefRepository->findBy(['cartId' => $cart->id, 'offer' => $offer]);

            /* iterate over dealt offers in cart */
            foreach ($dealtCartRefs as $dealtCartRef) {
                $cartProductId = $dealtCartRef->getProductId();
                $cartProductAttributeId = $dealtCartRef->getProductAttributeId();
                if (isset($cartProductsIndex[$cartProductId][$cartProductAttributeId])) {
                    /* we have a match in the cart */
                    $cartProduct = $cartProductsIndex[$cartProductId][$cartProductAttributeId];
                    $quantity += $cartProduct['cart_quantity'];
                } else {
                    /*
                     * we should delete the DealtCartProductRef reference as the product id/attribute_id pair could not be
                     * found in the current cart
                     */
                    $this->dealtCartRefRepository->delete($dealtCartRef->getId());
                }
            }

            $offerProductId = $offer->getDealtProductId();
            $newQty = (int) $quantity;
            $currentQty = (int) $cartProductsIndex[$offerProductId][0]['cart_quantity'];

            if ($newQty != $currentQty) {
                $delta = $newQty - $currentQty;
                $cart->updateQty(abs($delta), $offerProductId, null, false, $delta > 0 ? 'up' : 'down');
            }
        }
    }

    /**
     * Computes new subtotals for the dealt cart
     * by splitting the totals between actual products
     * and dealt services in cart
     *
     * @param Cart $cart
     * @param mixed $presentedCart
     * @param int $products_count
     *
     * @return void
     */
    protected function sanitizeSubTotals(Cart $cart, &$presentedCart, $products_count)
    {
        $cartProductsIndex = Helpers::indexCartProducts($cart, true);
        $offers = $this->offerRepository->getDealtOffersFromCart($cart);

        $dealtTotal = 0;
        foreach ($offers as $offer) {
            $dealtProductId = $offer->getDealtProductId();
            if (isset($cartProductsIndex[$dealtProductId])) {
                $dealtTotal += $cartProductsIndex[$dealtProductId][0]['total_wt'];
            }
        }

        $total = $presentedCart['subtotals']['products']['amount'];
        $total_without_services = $total - $dealtTotal;

        if ($dealtTotal != 0) {
            $presentedCart['subtotals']['dealt_total'] = [
                'type' => 'dealt_total',
                'label' => $this->module->getTranslator()->trans('Service(s)', [], 'Modules.Dealtmodule.Shop'),
                'amount' => $dealtTotal,
                'value' => Helpers::formatPrice($dealtTotal),
            ];

            $presentedCart['subtotals']['products']['amount'] = $total_without_services;
            $presentedCart['subtotals']['products']['value'] = Helpers::formatPrice($total_without_services);

            $presentedCart['summary_string'] = $products_count === 1 ?
                $this->module->getTranslator()->trans('1 item', [], 'Shop.Theme.Checkout') :
                $this->module->getTranslator()->trans('%count% items', ['%count%' => $products_count], 'Shop.Theme.Checkout');
        }
    }

    /**
     * Checks wether a cart has at least one service
     *
     * @param string $cartId
     *
     * @return bool
     */
    public function isCartAttachedToService($cartId)
    {
        return $this->dealtCartRefRepository->findOneByCartId($cartId) != null;
    }

    /**
     * @param DealtModule $module
     *
     * @return void
     */
    public function setModule(DealtModule $module)
    {
        $this->module = $module;
    }
}
