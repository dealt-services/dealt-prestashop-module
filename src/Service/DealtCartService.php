<?php

declare(strict_types=1);

namespace DealtModule\Service;

use DealtModule\Repository\DealtMissionCategoryRepository;
use DealtModule;
use Product;
use Context;
use DealtModule\Action\DealtAction;
use DealtModule\Entity\DealtMissionCategory;

/**
 * Dealt Cart class allows interacting with 
 * the prestashop cart and enforcing constraints
 * for Dealt virtual products
 */
final class DealtCartService
{
  /** @var DealtModule */
  private $module;

  /** @var DealtMissionCategoryRepository */
  private $missionCategoryRepository;

  /**
   * @param DealtModule $module
   */
  public function __construct($module)
  {
    $this->module = $module;
    $this->missionCategoryRepository = $this->module->get('dealtmodule.doctrine.dealt.mission.category.repository');
  }

  /**
   * @param int $productId
   * @return array|null
   */
  public function getMissionDataForProduct($productId)
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

    /** @var DealtMissionCategory|null */
    $missionCategory = $this
      ->missionCategoryRepository
      ->findOneBy(['categoryId' => $categories]);

    if ($missionCategory != null) {
      $mission = $missionCategory->getMission();
      $missionProduct = $mission->getVirtualProduct();

      /* retrieve the cover image */
      $img = $missionProduct->getCover($missionProduct->id);
      $missionImage = Context::getContext()->link->getImageLink(
        $missionProduct->name[Context::getContext()->language->id],
        (int)$img['id_image'],

      );
    }

    $productInCart = $this->isProductInCart($productId);


    return [
      'productInCart' => $productInCart,
      'mission' => $mission,
      'missionProduct' => $missionProduct,
      'missionImage' => $missionImage,
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
