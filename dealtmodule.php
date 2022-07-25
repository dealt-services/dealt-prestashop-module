<?php

use DealtModule\Checkout\DealtCheckoutStep;
use DealtModule\Database\DealtInstaller;
use DealtModule\Service\DealtAPIService;
use DealtModule\Service\DealtCartService;
use DealtModule\Service\DealtOrderService;
use DealtModule\Service\DealtProductService;
use DealtModule\Tools\Helpers;
use PrestaShopBundle\Entity\Repository\TabRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class DealtModule extends Module
{
    /** @var string */
    public static $DEALT_MAIN_TAB_NAME = 'DEALTMODULE';

    /** @var array<string, string>[] */
    public static $DEALT_TABS = [
        [
            'class_name' => 'DEALTMODULE',
            'icon' => 'settings',
            'parent_class_name' => 'IMPROVE',
            'name' => 'Dealt',
        ],
        [
            'route_name' => 'admin_dealt_configure',
            'class_name' => 'AdminDealtConfigurationController',
            'parent_class_name' => 'DEALTMODULE',
            'name' => 'Configure',
        ],
        [
            'route_name' => 'admin_dealt_offer_list',
            'class_name' => 'AdminDealtOfferController',
            'parent_class_name' => 'DEALTMODULE',
            'name' => 'Offers configuration',
        ],
        [
            'route_name' => 'admin_dealt_mission_list',
            'class_name' => 'AdminDealtMissionController',
            'parent_class_name' => 'DEALTMODULE',
            'name' => 'Missions',
        ],
    ];

    /** @var string[] */
    public static $DEALT_HOOKS = [
        'displayProductAdditionalInfo',
        'displayDealtAssociatedOffer',
        'displayDealtAssociatedOfferModal',
        'displayDealtSubtotalModal',
        'displayDealtOrderLine',
        'displayDealtOrderConfirmation',
        'actionFrontControllerSetMedia',
        'actionPresentCart',
        'actionPresentOrder',
        'actionCartSave',
        'actionCheckoutRender',
        'actionPaymentConfirmation',
    ];

    /** @var DealtCartService|null */
    protected $cartService = null;

    /** @var DealtApiService|null */
    protected $apiService = null;

    /** @var DealtProductService|null */
    protected $productService = null;

    /** @var DealtOrderService|null */
    protected $orderService = null;

    public function __construct()
    {
        $this->name = 'dealtmodule';
        $this->tab = 'administration';
        $this->version = '0.1.1';
        $this->author = 'Dealt Developers';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.8',
            'max' => '1.7.99',
        ];
        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->trans('Dealt Module', [], 'Modules.Dealtmodule.Admin');
        $this->description = $this->trans('The official Dealt prestashop module.', [], 'Modules.Dealtmodule.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Dealtmodule.Admin');

        if (!Configuration::get('dealtmodule')) {
            $this->warning = $this->trans('No name provided', [], 'Modules.Dealtmodule.Admin');
        }
    }

    /**
     * @return bool
     */
    public function install()
    {
        $installer = $this->getInstaller();

        return parent::install() && $this->installTabs() && $this->registerHook(static::$DEALT_HOOKS)
            && $installer->install();
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $installer = $this->getInstaller();

        return parent::uninstall()
            && $this->uninstallTabs()
            && $installer->uninstall();
    }

    /**
     * @return void
     */
    public function getContent()
    {
        /*
         * This uses the matching with the route ps_controller_tabs_configure via the _legacy_link property
         * See https://devdocs.prestashop.com/1.7/development/architecture/migration-guide/controller-routing
         */
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminDealtConfigurationController')
        );
    }

    /**
     * Instantiates the DealtInstaller module if needed :
     * During the installation process, the module's services are not available
     * as they are not registered yet.
     *
     * @return DealtInstaller
     */
    private function getInstaller()
    {
        try {
            $installer = $this->get('dealtmodule.installer');
        } catch (Exception $_) {
            $installer = null;
        }

        if (!$installer) {
            $installer = new DealtInstaller(
                $this->get('doctrine.dbal.default_connection'),
                $this->getContainer()->getParameter('database_prefix'),
                null /* repository not available yet ! */
            );
        }

        return $installer;
    }

    /**
     * @return bool
     */
    private function installTabs()
    {
        /** @var TabRepository */
        $tabRepo = $this->get('prestashop.core.admin.tab.repository');

        foreach (static::$DEALT_TABS as $tabDefinition) {
            $tabId = (int) $tabRepo->findOneIdByClassName($tabDefinition['class_name']);
            if (!$tabId) {
                $tabId = null;
            }

            $tab = new Tab($tabId);
            $tab->active = true;
            $tab->class_name = $tabDefinition['class_name'];
            if (isset($tabDefinition['route_name'])) {
                $tab->route_name = $tabDefinition['route_name'];
            }
            if (isset($tabDefinition['icon'])) {
                $tab->icon = $tabDefinition['icon'];
            }

            $tab->name = [];
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $this->trans($tabDefinition['name'], [], 'Modules.Dealtmodule.Admin', $lang['locale']);
            }
            $tab->id_parent = intval($tabRepo->findOneIdByClassName($tabDefinition['parent_class_name']));
            $tab->module = $this->name;
            $tab->wording_domain = 'Modules.Dealtmodule.Admin';
            $tab->wording = $tabDefinition['name'];

            $tab->save();
        }

        return true;
    }

    /**
     * @return bool
     */
    private function uninstallTabs()
    {
        /** @var TabRepository */
        $tabRepo = $this->get('prestashop.core.admin.tab.repository');

        foreach (static::$DEALT_TABS as $tabDefinition) {
            $tabId = (int) $tabRepo->findOneIdByClassName($tabDefinition['class_name']);
            if (!$tabId) {
                continue;
            }

            $tab = new Tab($tabId);
            $tab->delete();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * @return void
     */
    public function hookActionFrontControllerSetMedia()
    {
        /* register dealt stylesheets */
        $css = '/modules/' . $this->name . '/views/css/dealt.common.css';
        $this->context->controller->registerStylesheet(sha1($css), $css);

        if ('product' === $this->context->controller->php_self) {
            $js = '/modules/' . $this->name . '/views/public/dealt.front.offer.product.bundle.js';
            $this->context->controller->registerJavascript(sha1($js), $js);
        }

        if ('cart' === $this->context->controller->php_self) {
            $js = '/modules/' . $this->name . '/views/public/dealt.front.offer.cart.bundle.js';
            $this->context->controller->registerJavascript(sha1($js), $js);
        }

        Media::addJsDef(['DealtGlobals' => [
            'actions' => [
                'cart' => Context::getContext()->link->getModuleLink(
                    strtolower(DealtModule::class),
                    'cart',
                    ['ajax' => true],
                ),
                'api' => Context::getContext()->link->getModuleLink(
                    strtolower(DealtModule::class),
                    'api',
                    ['ajax' => true],
                ),
            ],
        ]]);
    }

    /**
     * @param mixed $params
     *
     * @return void
     */
    public function hookActionPresentCart($params)
    {
        $cartService = $this->getCartService();
        $presentedCart = &$params['presentedCart'];
        $cartService->sanitizeCartPresenter($presentedCart);
    }

    /**
     * @param mixed $params
     *
     * @return void
     */
    public function hookActionPresentOrder($params)
    {
        /* we only want to affect the confirmation page */
        if ('order-confirmation' !== $this->context->controller->php_self) {
            return;
        }

        $orderService = $this->getOrderService();
        $presentedOrder = &$params['presentedOrder'];
        $orderService->sanitizeOrderPresenter($presentedOrder);
    }

    /**
     * @param mixed $params
     *
     * @return void
     */
    public function hookActionCheckoutRender($params)
    {
        $cartService = $this->getCartService();
        $cartHasService = $cartService->isCartAttachedToService($params['cart']->id);

        if (!$cartHasService) {
            return;
        }

        /** @var CheckoutProcess */
        $checkoutProcess = $params['checkoutProcess'];

        $steps = $checkoutProcess->getSteps();
        $deliveryStepIdx = array_search('checkout-addresses-step', array_map(function (CheckoutStepInterface $step) {
            return $step->getIdentifier();
        }, $steps));

        $dealtStep = new DealtCheckoutStep(
            $this->context,
            $this->getTranslator(),
            $this->getAPIService(),
            $this->get('doctrine.orm.entity_manager'),
            $this->get('dealtmodule.presenter.dealt.offer')
        );

        $dealtStep->setCheckoutProcess($checkoutProcess);

        array_splice($steps, $deliveryStepIdx + 1, 0, [$dealtStep]);
        $checkoutProcess->setSteps($steps);
    }

    /**
     * @param mixed $data
     *
     * @return void
     */
    public function hookActionCartSave($data)
    {
        $cartService = $this->getCartService();
        $cartService->sanitizeDealtCart($data['cart']->id);
    }

    public function hookActionPaymentConfirmation($data)
    {
        $orderId = intval($data['id_order']);
        $this->getOrderService()->handleOrderPayment($orderId);
    }

    /**
     * DisplayProductActions hook
     * display hook data if current product matches a dealt offer
     * via its category
     *
     * @return string|null
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        $productService = $this->getProductService();

        if (isset($params['product'])) {
            $productId = (int) $params['product']['id'];
            $productAttributeId = (int) $params['product']['id_product_attribute'];
            $data = $productService->presentOfferForProduct($productId, $productAttributeId);

            if ($data == null) {
                return null;
            }

            $this->smarty->assign($data);
            return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayProductAdditionalInfo.tpl');
        }
    }

    /**
     * @param mixed $params
     *
     * @return string|null
     */
    public function hookDisplayDealtAssociatedOffer($params)
    {
        if (!isset($params['product']['dealt'])) {
            return null;
        }

        $this->smarty->assign($params['product']['dealt']);

        return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayDealtAssociatedOffer.tpl');
    }

    /**
     * @param mixed $params
     *
     * @return string|null
     */
    public function hookDisplayDealtAssociatedOfferModal($params)
    {
        if (!isset($params['product']['dealt'])) {
            return null;
        }

        $this->smarty->assign($params['product']['dealt']);

        return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayDealtAssociatedOfferModal.tpl');
    }

    /**
     * @param mixed $params
     *
     * @return string|null
     */
    public function hookDisplayDealtSubtotalModal($params)
    {
        if (!isset($params['cart'])) {
            return null;
        }

        $this->smarty->assign($params['cart']);

        return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayDealtSubtotalModal.tpl');
    }

    /**
     * @param mixed $params
     *
     * @return string|null
     */
    public function hookDisplayDealtOrderConfirmation($params)
    {
        if ((!isset($params['order']) && !isset($params['order']['hasDealtServices'])) || $params['order']['hasDealtServices'] == false) {
            return null;
        }

        return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayDealtOrderConfirmation.tpl');
    }

    /**
     * @param mixed $params
     *
     * @return string|null
     */
    public function hookDisplayDealtOrderLine($params)
    {
        if (!isset($params['product']['dealt'])) {
            return null;
        }

        $this->smarty->assign($params['product']['dealt']);

        return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayDealtOrderLine.tpl');
    }

    /**
     * @return DealtCartService
     */
    public function getCartService()
    {
        if ($this->cartService instanceof DealtCartService) {
            return $this->cartService;
        }
        /** @var DealtCartService */
        $cartService = $this->get('dealtmodule.dealt.cart.service');
        $this->cartService = $cartService;
        $this->cartService->setModule($this);

        return $this->cartService;
    }

    /**
     * @return DealtAPIService
     */
    public function getAPIService()
    {
        if ($this->apiService instanceof DealtAPIService) {
            return $this->apiService;
        }
        /** @var DealtAPIService */
        $apiService = $this->get('dealtmodule.dealt.api.service');
        $this->apiService = $apiService;

        return $this->apiService;
    }

    /**
     * @return DealtProductService
     */
    public function getProductService()
    {
        if ($this->productService instanceof DealtProductService) {
            return $this->productService;
        }
        /** @var DealtProductService */
        $productService = $this->get('dealtmodule.dealt.product.service');
        $this->productService = $productService;

        return $this->productService;
    }

    /**
     * @return DealtOrderService
     */
    public function getOrderService()
    {
        if ($this->orderService instanceof DealtOrderService) {
            return $this->orderService;
        }
        /** @var DealtOrderService */
        $orderService = $this->get('dealtmodule.dealt.order.service');
        $this->orderService = $orderService;

        return $this->orderService;
    }
}
