<?php

declare(strict_types=1);

namespace DealtModule\Service;

use DealtModule\Repository\DealtOfferCategoryRepository;
use DealtModule\Repository\DealtOfferRepository;
use DealtModule\Entity\DealtOfferCategory;
use DealtModule\Action\DealtAPIAction;
use DealtModule\Action\DealtCartAction;
use DealtModule\Entity\DealtOffer;
use DealtModule;
use Product;
use Context;
use DealtModule\Entity\DealtCartProduct;
use DealtModule\Repository\DealtCartProductRepository;
use Exception;

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

  /**
   * @param DealtOfferRepository $offerRepository
   * @param DealtOfferCategoryRepository $offerCategoryRepository
   * @param DealtCartProductRepository $cartProductRepository
   */
  public function __construct($offerRepository, $offerCategoryRepository, $cartProductRepository)
  {
    $this->offerRepository = $offerRepository;
    $this->offerCategoryRepository = $offerCategoryRepository;
    $this->cartProductRepository = $cartProductRepository;
  }

  /**
   * @param int $productId
   * @return array|null
   */
  public function getOfferDataForProduct($productId)
  {
    $product = new Product($productId);
    $categories = $product->getCategories();

    if (empty($categories)) return null;
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

    if ($offerCategory == null) return null;
    $offer = $offerCategory->getOffer();
    $offerProduct = $offer->getDealtProduct();

    /* retrieve the cover image */
    $img = $offerProduct->getCover($offerProduct->id);
    $offerImage = Context::getContext()->link->getImageLink(
      $offerProduct->name[Context::getContext()->language->id],
      (int)$img['id_image'],

    );

    return [
      'cartProduct' => $this->getProductFromCart($productId),
      'offer' => $offer,
      'offerProduct' => $offerProduct,
      'offerImage' => $offerImage,
      'addToCartAction' => Context::getContext()->link->getModuleLink(
        strtolower(DealtModule::class),
        'cart',
        [
          "ajax" => true,
          "action" => DealtCartAction::$ADD_TO_CART,
          "dealtOfferId" => $offer->getDealtOfferId(),
          "productId" => $productId
        ]
      ),
      'offerAvailabilityAction' => Context::getContext()->link->getModuleLink(
        strtolower(DealtModule::class),
        'api',
        [
          "ajax" => true,
          "action" => DealtAPIAction::$AVAILABILITY,
          "dealtOfferId" => $offer->getDealtOfferId()
        ]
      )
    ];
  }

  /**
   * Attaches a dealt product to a product
   * currently in the prestashop cart and syncs
   * their quantities
   *
   * @param string $dealtOfferId
   * @param int $productId
   * @return bool
   */
  public function addDealtOfferToCart($dealtOfferId, $productId)
  {
    /** @var DealtOffer|null */
    $offer = $this->offerRepository->findOneBy(['dealtOfferId' => $dealtOfferId]);
    if ($offer == null) throw new Exception('Unknown Dealt offer id');

    $cart = Context::getContext()->cart;
    $cartProduct = $this->getProductFromCart($productId);
    if ($cartProduct == null) throw new Exception('Cannot attach dealt offer to a product which is not currently in the cart');

    $dealtCartProduct = $this->getProductFromCart($offer->getDealtProductId());
    $quantity = (int) $cartProduct['quantity'] - (isset($dealtCartProduct['quantity']) ?
      (int) $dealtCartProduct['quantity'] :
      0
    );

    $this->cartProductRepository->create($cart->id, $productId, $offer);

    /* the quantities of a product and its attached offer must always be in sync */
    return $cart->updateQty(
      $quantity,
      $offer->getDealtProductId(),
      null,
      false
    );
  }

  /**
   * Filters in place the presented cart data
   * adds dealt specific data to products attached to a dealt offer
   *
   * @param array $presentedCart
   * @return void
   */
  public function sanitizeDealtCart(&$presentedCart)
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
        if ($dealtCartProduct->getProductId() == $cartProduct['id_product']) {
          $offer = $dealtCartProduct->getOffer();
          $cartProduct['dealt'] = [
            'cartProduct' => $this->getProductFromCart($offer->getDealtProductId()),
            'offer' => $offer
          ];
        }
      }
    }
  }

  /**
   * Iterates over the products in the current context's
   * cart and returns the first match
   *
   * @param int $productId
   * @return array|null
   */
  protected function getProductFromCart($productId)
  {
    $cartProducts = Context::getContext()->cart->getProducts();
    foreach ($cartProducts as $cartProduct) {
      if ((int) $cartProduct['id_product'] == $productId) {
        return $cartProduct;
      }
    }

    return null;
  }
}
