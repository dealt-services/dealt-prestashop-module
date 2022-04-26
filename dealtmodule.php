<?php

use DealtModule\Database\DealtInstaller;
use DealtModule\Repository\DealtMissionCategoryRepository;
use DealtModule\Entity\DealtMissionCategory;

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
  static $DEALT_HOOKS = ['displayProductAdditionalInfo'];

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

    return parent::install()
      && $installer->install()
      && $this->installTabs()
      && $this->registerHook(static::$DEALT_HOOKS);
  }

  public function uninstall()
  {
    $installer = $this->getInstaller();

    return parent::uninstall()
      && $installer->uninstall()
      && $this->uninstallTabs();
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
    $categories = $product->getCategories();

    /** @var DealtMissionCategoryRepository */
    $repo = $this->get('dealtmodule.doctrine.dealt.mission.category.repository');

    if (!empty($categories)) {
      /**
       * Find only first match - we may have multiple results
       * but this can only be caused either by :
       * - a category conflict due to a misconfiguration
       * - matching a parent/child category
       */
      /** @var DealtMissionCategory|null */
      $missionCategory = $repo->findOneBy(['categoryId' => $categories]);

      if ($missionCategory != null) {
        $mission = $missionCategory->getMission();
        $missionProduct = $mission->getVirtualProduct();

        /* retrieve the cover image */
        $img = $missionProduct->getCover($missionProduct->id);

        $missionImage = Context::getContext()->link->getImageLink(
          $missionProduct->name[Context::getContext()->language->id],
          (int)$img['id_image'],

        );

        $this->smarty->assign(['missionProduct' => $missionProduct, 'missionImage' => $missionImage]);
        return $this->fetch('module:dealtmodule/views/templates/front/hookDisplayProductAdditionalInfo.tpl');
      }
    }

    return null;
    return "<div>$productId (categories : $categories)</div>";
  }
}
