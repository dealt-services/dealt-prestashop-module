<?php

declare(strict_types=1);

namespace DealtModule\Service;

use Cart;
use Context;
use DealtModule;
use DealtModule\Entity\DealtCartProduct;
use DealtModule\Entity\DealtOffer;
use DealtModule\Entity\DealtOfferCategory;
use DealtModule\Repository\DealtCartProductRepository;
use DealtModule\Repository\DealtOfferCategoryRepository;
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

    /** @var DealtOfferCategoryRepository */
    private $offerCategoryRepository;

    /** @var DealtCartProductRepository */
    private $cartProductRepository;

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
     * @param DealtOfferCategoryRepository $offerCategoryRepository
     * @param DealtCartProductRepository $cartProductRepository
     */
    public function __construct(
        $offerRepository,
        $offerCategoryRepository,
        $cartProductRepository
    ) {
        $this->offerRepository = $offerRepository;
        $this->offerCategoryRepository = $offerCategoryRepository;
        $this->cartProductRepository = $cartProductRepository;
    }

    /**
     * @param int $productId
     * @param int|int[] $groupValues
     *
     * @return int
     */
    public function getProductAttributeIdFromGroup($productId, $groupValues)
    {
        return Product::getIdProductAttributeByIdAttributes(
            $productId,
            $groupValues
        );
    }

    /**
     * @param int $productId
     * @param int|null $productAttributeId
     *
     * @return array<string, mixed>|null
     */
    public function getOfferDataForProduct($productId, $productAttributeId = null)
    {
        $offer = $this->getOfferFromProductCategories($productId);
        if ($offer == null) {
            return null;
        }

        return [
            'cartProduct' => $this->getProductFromCart($productId, $productAttributeId),
            'cartOffer' => $this->cartProductRepository->findOneBy(array_merge([
                'cartId' => (int) Context::getContext()->cart->id,
                'productId' => $productId,
                'offer' => $offer,
            ], $productAttributeId != null ? ['productAttributeId' => $productAttributeId] : [])),
            'productId' => $productId,
            'productAttributeId' => $productAttributeId,
            'offer' => $offer,
        ];
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
        $cartProduct = $this->getProductFromCart($productId, $productAttributeId);
        if ($cartProduct == null) {
            throw new Exception('Cannot attach dealt offer to a product which is not currently in the cart');
        }

        $this->cartProductRepository->create($cart->id, $productId, $productAttributeId, $offer);

        /* this will trigger the dealt cart sanitization via PS hooks */
        return $cart->updateQty(
            (int) $cartProduct['quantity'],
            $offer->getDealtProductId(),
            null,
            false
        );
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
        /** @var DealtCartProduct[] */
        $dealtCartProducts = $this->cartProductRepository->findBy(['cartId' => $cart->id]);

        $dealtCartDealtProductIds = array_map(function (DealtCartProduct $dealtCartProduct) {
            return $dealtCartProduct
                ->getOffer()
                ->getDealtProductId();
        }, $dealtCartProducts);

        $presentedCart['products'] = array_filter($presentedCart['products'], function ($presentedCartProduct) use ($dealtCartDealtProductIds) {
            return !in_array(
                $presentedCartProduct['id_product'],
                $dealtCartDealtProductIds
            );
        });

        foreach ($presentedCart['products'] as &$cartProduct) {
            foreach ($dealtCartProducts as $dealtCartProduct) {
                if (
                    $dealtCartProduct->getProductId() == $cartProduct['id_product'] &&
                    $dealtCartProduct->getProductAttributeId() == $cartProduct['id_product_attribute']
                ) {
                    $offer = $dealtCartProduct->getOffer();
                    $cartProduct['dealt'] = [
                        'cartProduct' => $this->getProductFromCart($offer->getDealtProductId()),
                        'offer' => $offer->toArray(),
                        'offerPrice' => $offer->getFormattedPrice($cartProduct['quantity']),
                    ];
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
     * @param int $cartId
     *
     * @return void
     */
    public function sanitizeDealtCart($cartId)
    {
        if ($this->cartSanitized) {
            return;
        }
        $this->cartSanitized = true;

        $cart = new Cart($cartId);
        $offers = $this->getDealtOffersFromCart($cart);
        $cartProductsIndex = $this->indexCartProducts($cart);

        /*
                 * If we have dealt offers present in the cart
                 * we need to ensure their quantities match their
                 * attached products
                 */
        foreach ($offers as $offer) {
            $quantity = 0;

            /** @var DealtCartProduct[] */
            $dealtCartProducts = $this->cartProductRepository->findBy(['cartId' => $cart->id, 'offer' => $offer]);

            /* iterate over dealt offers in cart */
            foreach ($dealtCartProducts as $dealtCartProduct) {
                $cartProductId = $dealtCartProduct->getProductId();
                $cartProductAttributeId = $dealtCartProduct->getProductAttributeId();
                if (isset($cartProductsIndex[$cartProductId][$cartProductAttributeId])) {
                    /* we have a match in the cart */
                    $cartProduct = $cartProductsIndex[$cartProductId][$cartProductAttributeId];
                    $quantity += $cartProduct['quantity'];
                } else {
                    /*
                                         * we should delete the DealtCartProduct reference as the product id/attribute_id pair could not be
                                         * found in the current cart
                                         */
                    $this->cartProductRepository->delete($dealtCartProduct->getId());
                }
            }

            $offerProductId = $offer->getDealtProductId();
            $newQty = (int) $quantity;
            $currentQty = (int) $cartProductsIndex[$offerProductId][0]['quantity'];

            if ($newQty != $currentQty) {
                $delta = $newQty - $currentQty;
                $cart->updateQty(abs($delta), $offerProductId, null, false, $delta > 0 ? 'up' : 'down');
            }
        }
    }

    /**
     * @param int $productId
     *
     * @return DealtOffer|null
     */
    protected function getOfferFromProductCategories($productId)
    {
        $product = new Product($productId);
        $categories = $product->getCategories();

        if (empty($categories)) {
            return null;
        }
        /**
         * Find only first match - we may have multiple results
         * but this can only be caused either by :
         * - a category conflict due to a misconfiguration
         * - matching a parent/child category
         */

        /** @var DealtOfferCategory|null */
        $offerCategory = $this
            ->offerCategoryRepository
            ->findOneBy(['categoryId' => $categories]);

        if ($offerCategory == null) {
            return null;
        }

        return $offerCategory->getOffer();
    }

    /**
     * Iterates over the products in the current context's
     * cart and returns the first match
     *
     * @param int $productId
     * @param int $productAttributeId
     *
     * @return mixed|null
     */
    protected function getProductFromCart($productId, $productAttributeId = null)
    {
        $cartProducts = Context::getContext()->cart->getProducts();

        foreach ($cartProducts as $cartProduct) {
            if (
                (int) $cartProduct['id_product'] == $productId &&
                ($productAttributeId == null || ((int) $cartProduct['id_product_attribute'] == $productAttributeId))
            ) {
                return $cartProduct;
            }
        }

        return null;
    }

    /**
     * Resolves the dealt offers from the current
     * cart products.
     *
     * @param Cart $cart
     *
     * @return DealtOffer[]
     */
    protected function getDealtOffersFromCart(Cart $cart)
    {
        $cartProducts = $cart->getProducts();
        $cartProductIds = array_map(function ($cartProduct) {
            return (int) $cartProduct['id_product'];
        }, $cartProducts);

        return $this->offerRepository->findBy(['dealtProductId' => $cartProductIds]);
    }

    /**
     * Creates an indexed multi-dimensional array of the current cart
     * [productId][attributeId] product
     * Useful for quick lookup.
     *
     * @param Cart $cart
     *
     * @return array<int, array<int, mixed>>
     */
    protected function indexCartProducts(Cart $cart)
    {
        $cartProducts = [];

        foreach ($cart->getProducts() as $cartProduct) {
            $productId = $cartProduct['id_product'];
            $productAttributeId = $cartProduct['id_product_attribute'];

            if (!isset($cartProducts[$productId])) {
                $cartProducts[$productId] = [];
            }
            $cartProducts[$productId][$productAttributeId] = $cartProduct;
        }

        return $cartProducts;
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
        $cartProductsIndex = $this->indexCartProducts($cart);
        $offers = $this->getDealtOffersFromCart($cart);

        $dealtTotal = 0;
        foreach ($offers as $offer) {
            $dealtProductId = $offer->getDealtProductId();
            if (isset($cartProductsIndex[$dealtProductId])) {
                $dealtTotal += $cartProductsIndex[$dealtProductId][0]['total'];
            }
        }

        $total = $presentedCart['subtotals']['products']['amount'];
        $total_without_services = $total - $dealtTotal;

        if ($dealtTotal != 0) {
            $presentedCart['subtotals']['dealt_total'] = [
                'type' => 'dealt_total',
                'label' => $this->module->translate('Service(s)', [], 'Modules.DealtModule.Front'),
                'amount' => $dealtTotal,
                'value' => Helpers::formatPrice($dealtTotal),
            ];

            $presentedCart['subtotals']['products']['amount'] = $total_without_services;
            $presentedCart['subtotals']['products']['value'] = Helpers::formatPrice($total_without_services);

            $presentedCart['summary_string'] = $products_count === 1 ?
                $this->module->translate('1 item', [], 'Shop.Theme.Checkout') :
                $this->module->translate('%count% items', ['%count%' => $products_count], 'Shop.Theme.Checkout');
        }
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
