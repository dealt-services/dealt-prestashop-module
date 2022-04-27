<?php

declare(strict_types=1);

use DealtModule\Action\DealtAction;

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
    $this->setResponseHeaders();
    $this->ajaxRender(json_encode([
      "ok" => true,
      "action" => $action
    ]));
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
      "error" => "Unknown dealt action"
    ]));

    exit;
  }

  protected function setResponseHeaders()
  {
    ob_end_clean();
    header('Content-Type: application/json');
  }
}
