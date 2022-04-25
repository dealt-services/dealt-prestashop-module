<?php

use DealtModule\Database\DealtInstaller;

if (!defined('_PS_VERSION_')) exit;
if (file_exists(__DIR__ . '/vendor/autoload.php')) require_once __DIR__ . '/vendor/autoload.php';

class DealtModule extends Module
{
  static $DEALT_PRODUCT_CATEGORY_NAME = "__dealt__";
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
    return parent::install() && $this->registerHook(['displayProductAdditionalInfo']) && $this->installTabs();
  }

  public function uninstall()
  {
    $this->unsetup();
    return parent::uninstall() && $this->uninstallTabs();
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
        $this->getContainer()->getParameter('database_prefix')
      );
    }

    return $installer;
  }

  private function setup()
  {
    /* create internal DealtModule category */
    $match = Category::searchByName(null, static::$DEALT_PRODUCT_CATEGORY_NAME, true);

    if (empty($match)) {
      $idLang = (int) Context::getContext()->language->id;

      $category = new Category();
      $category->name = [$idLang => static::$DEALT_PRODUCT_CATEGORY_NAME];
      $category->link_rewrite = [$idLang => Tools::link_rewrite(static::$DEALT_PRODUCT_CATEGORY_NAME)];
      $category->active = false;
      $category->id_parent = Configuration::get('PS_ROOT_CATEGORY');
      $category->description = "Internal DealtModule category used for Dealt mission virtual products";
      $category->add();
    }

    /* create DealtModule SQL tables */
    $this->getInstaller()->createTables();
  }

  private function unsetup()
  {
    /* remove internal dealt category */
    $match = Category::searchByName(null, static::$DEALT_PRODUCT_CATEGORY_NAME, true);

    if (!empty($match)) {
      $category = new Category($match['id_category']);
      $category->delete();
    }
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

  /**
   * DisplayProductActions hook :
   * - from the current product id -> check wether
   *
   * @return void
   */
  public function hookDisplayProductAdditionalInfo()
  {
    $productId = Tools::getValue('id_product');
    $product = new Product($productId);
    $categories = json_encode($product->getCategories());

    return "<div>$productId (categories : $categories)</div>";
  }
}
