<?php

declare(strict_types=1);

namespace DealtModule\Database;

use DealtModule\Tools\Helpers;
use DealtModule\Repository\DealtMissionRepository;
use DealtModule\Entity\DealtMission;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use Category;
use Tools;
use Configuration;

final class DealtInstaller
{
  static $DEALT_PRODUCT_CATEGORY_NAME = "__dealt__";
  static $DEALT_SQL_TABLES = ["dealt_mission", "dealt_mission_category"];

  /**
   * @var Connection
   */
  private $connection;

  /**
   * @var string
   */
  private $dbPrefix;

  /**
   * @var DealtMissionRepository
   */
  private $missionRepository;

  /**
   * @param Connection $connection
   * @param string $dbPrefix
   */
  public function __construct(
    $connection,
    $dbPrefix,
    $missionRepository
  ) {
    $this->connection = $connection;
    $this->dbPrefix = $dbPrefix;
    $this->missionRepository = $missionRepository;
  }

  /**
   * Helper to check wether the API Key is valid
   * in the module's configuration
   *
   * @return boolean
   */
  static function isModuleConfigured()
  {
    $apiKey = Configuration::get('DEALTMODULE_API_KEY');
    return isset($apiKey) && !empty($apiKey);
  }

  /**
   * Helper to check wether the DealtModule is running
   * in production mode
   *
   * @return boolean
   */
  static function isProduction()
  {
    $prodEnv = Configuration::get('DEALTMODULE_PROD_ENV');
    return $prodEnv == true;
  }

  /**
   * @return bool
   */
  public function install()
  {
    $errors = $this->createTables();
    if (!empty($errors)) return false;

    return $this->createCategories();
  }

  /**
   * @return bool
   */
  public function uninstall()
  {
    $this->cleanUp();

    $errors = $this->dropTables();
    if (!empty($errors)) return false;

    return $this->deleteCategories();
  }

  /**
   * @return array
   *
   * @throws \Doctrine\DBAL\DBALException
   * @return mixed
   */
  private function createTables()
  {
    $errors = [];
    $this->dropTables();

    $sqlInstallDir = __DIR__ . '/../../resources/data/';
    $sqlQueries = str_replace('PREFIX_', $this->dbPrefix, array_map(function ($file) use ($sqlInstallDir) {
      return file_get_contents($sqlInstallDir . $file . ".sql");
    }, static::$DEALT_SQL_TABLES));

    try {
      foreach ($sqlQueries as $query) {
        if (empty($query)) continue;
        $this->connection->executeQuery($query);
      }
    } catch (Exception $e) {
      $errors[] = [
        'key' => json_encode($e),
        'parameters' => [],
        'domain' => 'Admin.Modules.Notification',
      ];

      $this->connection->rollBack();
    }

    return $errors;
  }

  /**
   * @return array
   *
   * @throws DBALException
   */
  private function dropTables()
  {
    $errors = [];

    try {
      foreach (static::$DEALT_SQL_TABLES as $tableName) {
        $sql = 'DROP TABLE IF EXISTS ' . $this->dbPrefix . $tableName;
        $this->connection->executeQuery($sql);
      }
    } catch (Exception $e) {
      $errors[] = [
        'key' => json_encode($e),
        'parameters' => [],
        'domain' => 'Admin.Modules.Notification',
      ];
    }

    return $errors;
  }

  /**
   * Create the internal DealtModule category
   * -> used for virtual dealt products
   *
   * @return bool
   */
  private function createCategories()
  {
    $match = Category::searchByName(null, static::$DEALT_PRODUCT_CATEGORY_NAME, true);

    if (empty($match)) {
      $category = new Category();
      $category->name = Helpers::createMultiLangField(static::$DEALT_PRODUCT_CATEGORY_NAME);
      $category->link_rewrite =  Helpers::createMultiLangField(Tools::link_rewrite(static::$DEALT_PRODUCT_CATEGORY_NAME));
      $category->active = false;
      $category->id_parent = Category::getRootCategory()->id;
      $category->description = "Internal DealtModule category used for Dealt mission virtual products";

      return $category->add();
    }

    return true;
  }

  /**
   * Deletes the DealtModule internal category
   *
   * @return bool
   */
  private function deleteCategories()
  {
    $match = Category::searchByName(null, static::$DEALT_PRODUCT_CATEGORY_NAME, true);

    if (!empty($match)) {
      $category = new Category($match['id_category']);
      return $category->delete();
    }

    return true;
  }

  /**
   * Clean-up DB data before module uninstall
   * 
   * @return bool
   */
  private function cleanUp()
  {
    /** @var DealtMission[] */
    $missions = $this->missionRepository->findAll();

    foreach ($missions as $mission) {
      $product = $mission->getVirtualProduct();
      $product->delete();
    }

    return true;
  }
}
