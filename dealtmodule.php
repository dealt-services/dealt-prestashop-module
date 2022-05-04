<?php

use DealtModule\Database\DealtInstaller;
use DealtModule\Service\DealtCartService;
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
  ];

    /** @var string[] */
    public static $DEALT_HOOKS = [
    'displayProductAdditionalInfo',
    'displayDealtAssociatedOffer',
    'displayDealtAssociatedOfferModal',
    'displayDealtSubtotalModal',
    'actionFrontControllerSetMedia',
    'actionPresentCart',
    'actionCartSave',
    'actionCartUpdateQuantityBefore',
  ];

    /** @var DealtCartService|null */
    protected $cartService = null;

    public function __construct()
    {
        $this->name = 'dealtmodule';
        $this->tab = 'administration';
        $this->version = '0.0.1';
        $this->author = 'Dealt Developers';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
      'min' => '1.7.8',
      'max' => '1.7.99',
    ];
        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->trans('Dealt Module', [], 'Modules.DealtModule.Admin');
        $this->description = $this->trans('The official Dealt prestashop module.', [], 'Modules.DealtModule.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.DealtModule.Admin');

        if (!Configuration::get('dealtmodule')) {
            $this->warning = $this->trans('No name provided', [], 'Modules.DealtModule.Admin');
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
        // This uses the matching with the route ps_controller_tabs_configure via the _legacy_link property
        // See https://devdocs.prestashop.com/1.7/development/architecture/migration-guide/controller-routing
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
                $tab->name[$lang['id_lang']] = $this->trans($tabDefinition['name'], [], 'Modules.DealtModule.Admin', $lang['locale']);
            }
            $tab->id_parent = intval($tabRepo->findOneIdByClassName($tabDefinition['parent_class_name']));
            $tab->module = $this->name;
            $tab->wording_domain = 'Modules.DealtModule.Admin';
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
     * @param mixed $data
     *
     * @return void
     */
    public function hookActionPresentCart($data)
    {
        $cartService = $this->getCartService();
        $presentedCart = &$data['presentedCart']; /* pass a pointer to the array as we want to mutate it */
        $cartService->sanitizeCartPresenter($presentedCart);
    }

    /**
     * @param mixed $data
     *
     * @return void
     */
    public function hookActionCartUpdateQuantityBefore($data)
    {
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

    /**
     * DisplayProductActions hook
     * display hook data if current product matches a dealt offer
     * via its category
     *
     * @return string|null
     */
    public function hookDisplayProductAdditionalInfo()
    {
        $cartService = $this->getCartService();
        $productId = (int) Tools::getValue('id_product');
        $groupValues = Tools::getValue('group'); /* available on product page ajax refresh */

        $productAttributeId = Tools::getValue('id_product_attribute');
        $productAttributeId = $productAttributeId != false ?
      $productAttributeId : (isset($groupValues) && $groupValues != false ?
        $cartService->getProductAttributeIdFromGroup($productId, array_values($groupValues))
        : null
      );

        $data = $cartService->getOfferDataForProduct($productId, $productAttributeId);
        if ($data == null) {
            return null;
        }

        $this->smarty->assign($data);

        return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayProductAdditionalInfo.tpl');
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
}

/*
 * Registering a hook when module already installed :
 *
 * if ($this->isRegisteredInHook('actionValidateCustomerAddressForm')) {
      $this->registerHook('actionCheckoutRender');
    }
 */
