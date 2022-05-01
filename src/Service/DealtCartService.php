<?php

declare(strict_types=1);

namespace DealtModule\Service;

use DealtModule\Repository\DealtOfferCategoryRepository;
use DealtModule\Action\DealtAction;
use DealtModule\Entity\DealtOfferCategory;
use DealtModule;
use Product;
use Context;

/**
 * Dealt Cart class allows interacting with 
 * the prestashop cart and enforcing constraints
 * for Dealt virtual products
 */
final class DealtCartService
{
  /** @var DealtModule */
  private $module;

  /** @var DealtOfferCategoryRepository */
  private $offerCategoryRepository;

  /**
   * @param DealtModule $module
   */
  public function __construct($module)
  {
    $this->module = $module;
    $this->offerCategoryRepository = $this->module->get('dealtmodule.doctrine.dealt.offer.category.repository');
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

    if ($offerCategory != null) {
      $offer = $offerCategory->getOffer();
      $offerProduct = $offer->getVirtualProduct();

      /* retrieve the cover image */
      $img = $offerProduct->getCover($offerProduct->id);
      $offerImage = Context::getContext()->link->getImageLink(
        $offerProduct->name[Context::getContext()->language->id],
        (int)$img['id_image'],

      );
    }

    $productInCart = $this->isProductInCart($productId);


    return [
      'productInCart' => $productInCart,
      'offer' => $offer,
      'offerProduct' => $offerProduct,
      'offerImage' => $offerImage,
      'availabilityUrl' => Context::getContext()->link->getModuleLink(
        strtolower(DealtModule::class),
        'api',
        [
          "ajax" => true,
          "action" => DealtAction::$AVAILABILITY
        ]
      )
    ];
  }

  /**
   * Checks wether a product is present in the cart
   *
   * @param int $productId
   * @return array|null
   */
  protected function isProductInCart($productId)
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
