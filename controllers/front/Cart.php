<?php

declare(strict_types=1);

use DealtModule\Action\DealtCartAction;
use DealtModule\Controller\Front\ModuleActionHandlerFrontController;
use DealtModule\Service\DealtCartService;

class DealtModuleCartModuleFrontController extends ModuleActionHandlerFrontController
{
  public $ssl = true;
  public $json = true;

  public function getModuleActionsClass()
  {
    return get_class(new DealtCartAction());
  }

  public function handleAction($action)
  {
    switch ($action) {
      case DealtCartAction::$ADD_TO_CART:
        return $this->handleAddToCart();
      case DealtCartAction::$GET_PRODUCT_ATTRIBUTE_ID:
        return $this->handleGetProductAttributeId();
    }
  }

  protected function handleAddToCart()
  {
    /** @var DealtCartService */
    $cartService = $this->get('dealtmodule.dealt.cart.service');

    return $cartService->addDealtOfferToCart(
      Tools::getValue('id_dealt_offer'),
      (int) Tools::getValue('id_product'),
      (int) Tools::getValue('id_product_attribute')
    );
  }

  protected function handleGetProductAttributeId()
  {
    return [
      "productAttributeId" => (int) Product::getIdProductAttributeByIdAttributes(
        Tools::getValue('id_product'),
        Tools::getValue('group')
      )
    ];
  }
}
