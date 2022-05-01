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
    }
  }

  protected function handleAddToCart()
  {
    /** @var DealtCartService */
    $cartService = $this->get('dealtmodule.dealt.cart.service');

    return $cartService->addDealtOfferToCart(
      Tools::getValue('dealtOfferId'),
      (int) Tools::getValue('productId')
    );
  }
}
