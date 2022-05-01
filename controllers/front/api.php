<?php

declare(strict_types=1);

use DealtModule\Action\DealtAction;
use DealtModule\Service\DealtAPIService;

class DealtModuleApiModuleFrontController extends ModuleFrontController
{
  public $ssl = true;
  public $json = true;

  public function postProcess()
  {
    $action = Tools::getValue('action');
    if ($action == false) $this->displayAjaxError("you must specify an action");
    if (!in_array($action, DealtAction::cases())) $this->displayAjaxError("unknown dealt action");

    $this->handleAction($action);
  }

  public function initContent()
  {
    $this->ajax = true;
    parent::initContent();
  }

  private function handleAction($action)
  {


    /** @var DealtAPIService */
    $client = $this->get('dealtmodule.dealt.api.service');
    $result = [];

    try {
      switch ($action) {
        case DealtAction::$AVAILABILITY:
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

  /**
   * @param string $error
   * @return void
   */
  private function displayAjaxError($error)
  {
    $this->setResponseHeaders();
    $this->ajaxRender(json_encode([
      "ok" => false,
      "error" => $error
    ]));

    exit;
  }

  protected function setResponseHeaders()
  {
    ob_end_clean();
    header('Content-Type: application/json');
  }
}
