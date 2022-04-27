<?php

declare(strict_types=1);

class DealtModuleApiModuleFrontController extends ModuleFrontController
{
  public $ssl = true;
  public $json = true;

  public function postProcess()
  {
    $action = Tools::getValue('dealt_action');
    if ($action == false) $this->displayAjaxError();
  }

  public function initContent()
  {
    $this->ajax = true;
    parent::initContent();
  }

  private function displayAjaxError()
  {
    ob_end_clean();
    header('Content-Type: application/json');

    $this->ajaxRender(json_encode([
      "ok" => false,
      "error" => "Unknown dealt action"
    ]));
  }
}
