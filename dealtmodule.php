<?php

use DealtModule\Action\DealtAction;
use DealtModule\Database\DealtInstaller;
use DealtModule\Repository\DealtMissionCategoryRepository;
use DealtModule\Entity\DealtMissionCategory;
use DealtModule\Service\DealtCartService;

if (!defined('_PS_VERSION_')) exit;
if (file_exists(__DIR__ . '/vendor/autoload.php')) require_once __DIR__ . '/vendor/autoload.php';

class DealtModule extends Module
{
  static $DEALT_MAIN_TAB_NAME = "DEALTMODULE";

  static $DEALT_TABS = [
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
      'route_name' => 'admin_dealt_missions_list',
      'class_name' => 'AdminDealtMissionController',
      'parent_class_name' => 'DEALTMODULE',
      'name' => 'Missions configuration',
    ]
  ];

  static $DEALT_HOOKS = [
    'actionFrontControllerSetMedia',
    'displayProductAdditionalInfo',
    'actionPresentCart'
  ];

  /** @var DealtCartService $cartService */
  protected $cartService;

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

  public function install()
  {
    $installer = $this->getInstaller();

    return parent::install() && $this->installTabs() && $this->registerHook(static::$DEALT_HOOKS)
      && $installer->install();
  }

  public function uninstall()
  {
    $installer = $this->getInstaller();

    return parent::uninstall()
      && $this->uninstallTabs()
      && $installer->uninstall();
  }

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

  private function installTabs()
  {
    $tabRepo = $this->get('prestashop.core.admin.tab.repository');

    foreach (static::$DEALT_TABS as $tabDefinition) {
      $tabId = (int) $tabRepo->findOneIdByClassName($tabDefinition['class_name']);
      if (!$tabId) $tabId = null;

      $tab = new Tab($tabId);
      $tab->active = 1;
      $tab->class_name = $tabDefinition['class_name'];
      if (isset($tabDefinition['route_name'])) $tab->route_name = $tabDefinition['route_name'];
      if (isset($tabDefinition['icon'])) $tab->icon = $tabDefinition['icon'];

      $tab->name = [];
      foreach (Language::getLanguages() as $lang) {
        $tab->name[$lang['id_lang']] = $this->trans($tabDefinition['name'], [], 'Modules.DealtModule.Admin', $lang['locale']);
      }
      $tab->id_parent = (int) $tabRepo->findOneIdByClassName($tabDefinition['parent_class_name']);
      $tab->module = $this->name;
      $tab->wording_domain = "Modules.DealtModule.Admin";
      $tab->wording = $tabDefinition['name'];

      $tab->save();
    }

    return true;
  }

  private function uninstallTabs()
  {
    $tabRepo = $this->get('prestashop.core.admin.tab.repository');

    foreach (static::$DEALT_TABS as $tabDefinition) {
      $tabId = (int) $tabRepo->findOneIdByClassName($tabDefinition['class_name']);
      if (!$tabId) continue;

      $tab = new Tab($tabId);
      $tab->delete();
    }

    return true;
  }

  public function isUsingNewTranslationSystem()
  {
    return true;
  }

  public function hookActionFrontControllerSetMedia()
  {
    if ('product' === $this->context->controller->php_self) {
      $jsModule = "/modules/" . $this->name . '/views/public/dealt.front.mission.product.bundle.js';
      $this->context->controller->registerJavascript(sha1($jsModule), $jsModule);
    }
  }

  /**
   * DisplayProductActions hook
   * display hook data if current product matches a dealt mission
   * via its category
   *
   * @return string|null
   */
  public function hookDisplayProductAdditionalInfo()
  {
    $productId = (int) Tools::getValue('id_product');
    $data = $this->getCartService()->getMissionDataForProduct($productId);
    if ($data == null) return;

    $this->smarty->assign($data);
    return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayProductAdditionalInfo.tpl');
  }

  /**
   * @param array $data
   * @return array
   */
  public function hookActionPresentCart($data)
  {
    $cartProducts =  &$data['presentedCart'];
  }

  /**
   * @return DealtCartService
   */
  protected function getCartService()
  {
    if ($this->cartService instanceof DealtCartService) return $this->cartService;
    $this->cartService = new DealtCartService($this);

    return $this->cartService;
  }
}
