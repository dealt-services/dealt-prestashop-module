<?php

declare(strict_types=1);

namespace DealtModule\Controller\Front;

use DealtModule\Action\ActionInterface;
use Error;
use ModuleFrontController;
use Tools;

abstract class ModuleActionHandlerFrontController extends ModuleFrontController
{
  public $ssl = true;
  public $json = true;

  /**
   * @param string $action
   * @return void
   */
  abstract function handleAction($action);

  /**
   * resolves the actions class for the module
   * @return string
   */
  abstract function getModuleActionsClass();

  public function initContent()
  {
    $this->ajax = true;
    parent::initContent();
  }

  public function postProcess()
  {
    $actionsClass = $this->getModuleActionsClass();
    $implements = class_implements($actionsClass);

    if (!isset($implements['DealtModule\Action\ActionInterface']))
      throw new Error('$ModuleActionHandlerFrontController:getModuleActionsClass\'s resulting class must implement ActionInterface');

    $action = Tools::getValue('action');
    if ($action == false) $this->displayAjaxError("you must specify an action");
    if (!in_array($action,  $actionsClass::cases())) $this->displayAjaxError("unknown action");

    $this->handleAction($action);
  }


  protected function setResponseHeaders()
  {
    ob_end_clean();
    header('Content-Type: application/json');
  }

  /**
   * @param string $error
   * @return void
   */
  public function displayAjaxError($error)
  {
    $this->setResponseHeaders();
    $this->ajaxRender(json_encode([
      "ok" => false,
      "error" => $error
    ]));

    exit;
  }
}
