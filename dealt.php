<?php

use Dealt\DealtSDK\DealtClient;

if (!defined('_PS_VERSION_')) {
  exit;
}

class DealtModule extends Module
{
  public function __construct()
  {
    $this->name = 'dealt';
    // $this->tab = 'administration';
    $this->version = '1.0.0';
    $this->author = 'Edvin CANDON';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = [
      'min' => '1.7',
      'max' => '1.7.99',
    ];
    $this->bootstrap = true;

    parent::__construct();
    $this->displayName = $this->l('dealt');
    $this->description = $this->l('The official Dealt prestashop module.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('dealt')) {
      $this->warning = $this->l('No name provided');
    }
  }

  public function install()
  {
    return parent::install();
  }

  public function uninstall()
  {
    return parent::uninstall();
  }
}
