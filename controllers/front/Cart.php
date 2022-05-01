<?php

declare(strict_types=1);

use DealtModule\Action\DealtCartAction;
use DealtModule\Controller\Front\ModuleActionHandlerFrontController;

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
    $result = [];

    try {
      $this->setResponseHeaders();
      $this->ajaxRender(json_encode([
        "ok" => true,
        "action" => $action,
        "result" => $result
      ]));
    } catch (Exception $e) {
      $this->displayAjaxError($e->getMessage());
    }
  }
}
