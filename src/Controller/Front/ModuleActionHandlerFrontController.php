<?php

declare(strict_types=1);

namespace DealtModule\Controller\Front;

use Exception;
use ModuleFrontController;
use Tools;

abstract class ModuleActionHandlerFrontController extends ModuleFrontController
{
    /**
     * @param string $action
     *
     * @return mixed
     */
    abstract public function handleAction($action);

    /**
     * resolves the actions class for the module
     *
     * @return string
     */
    abstract public function getModuleActionsClass();

    public function initContent()
    {
        $this->ajax = true;
        $this->json = true;
        $this->ssl = true;
        parent::initContent();
    }

    public function postProcess()
    {
        $actionsClass = $this->getModuleActionsClass();
        $action = Tools::getValue('action');

        try {
            if (!isset(class_implements($actionsClass)['DealtModule\Action\ActionInterface'])) {
                throw new Exception('ModuleActionHandlerFrontController:getModuleActionsClass\'s resulting class must implement ActionInterface');
            }

            if ($action == false) {
                throw new Exception('You must specify an action');
            }
            if (!in_array($action, $actionsClass::cases())) {
                throw new Exception('Unknown action');
            }

            $result = $this->handleAction($action);
            $this->setResponseHeaders();
            $this->ajaxRender(json_encode([
                'ok' => true,
                'action' => $action,
                'result' => $result,
            ]));
        } catch (Exception $e) {
            $this->displayAjaxError($e->getMessage());
        }
    }

    protected function setResponseHeaders()
    {
        ob_end_clean();
        header('Content-Type: application/json');
    }

    /**
     * @param string $error
     *
     * @return void
     */
    public function displayAjaxError($error)
    {
        $this->setResponseHeaders();
        $this->ajaxRender(json_encode([
            'ok' => false,
            'error' => $error,
        ]));

        exit;
    }
}
