<?php

declare(strict_types=1);

use DealtModule\Action\DealtAPIAction;
use DealtModule\Controller\Front\ModuleActionHandlerFrontController;
use DealtModule\Service\DealtAPIService;

class DealtModuleApiModuleFrontController extends ModuleActionHandlerFrontController
{
  public function getModuleActionsClass()
  {
    return get_class(new DealtAPIAction());
  }

  public function handleAction($action)
  {
    /** @var DealtAPIService */
    $client = $this->get('dealtmodule.dealt.api.service');
    $result = [];

    try {
      switch ($action) {
        case DealtAPIAction::$AVAILABILITY:
          $available = $client->checkAvailability(Tools::getValue('id_dealt_offer'), Tools::getValue('zip_code'));
          $result['available'] = $available;
          break;
      }

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
