<?php

declare(strict_types=1);

namespace DealtModule\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;

class DealtInstaller
{
  /**
   * @var Connection
   */
  private $connection;

  /**
   * @var string
   */
  private $dbPrefix;

  /**
   * @param Connection $connection
   * @param string $dbPrefix
   */
  public function __construct(
    $connection,
    $dbPrefix
  ) {
    $this->connection = $connection;
    $this->dbPrefix = $dbPrefix;
  }

  /**
   * @return array
   *
   * @throws \Doctrine\DBAL\DBALException
   */
  public function createTables()
  {
    $errors = [];
    $this->dropTables();
    $sqlInstallDir = __DIR__ . '/../../resources/data/';
    $sqlInstallFiles = ["dealt_mission.sql"];

    $sqlQueries = str_replace('PREFIX_', $this->dbPrefix, array_map(function ($file) use ($sqlInstallDir) {
      return file_get_contents($sqlInstallDir . $file);
    }, $sqlInstallFiles));

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
  public function dropTables()
  {
    $errors = [];
    $tableNames = ['dealt_mission'];

    try {
      foreach ($tableNames as $tableName) {
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
}
