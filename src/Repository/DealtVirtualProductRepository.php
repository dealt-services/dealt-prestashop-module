<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use Doctrine\DBAL\Connection;

/**
 * Repository class to help interacting with
 * the virtual products created by the dealtmodule
 */
class DealtVirtualProductRepository
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
   * LinkBlockRepository constructor.
   *
   * @param Connection $connection
   * @param string $dbPrefix
   */
  public function __construct(
    Connection $connection,
    string $dbPrefix
  ) {
    $this->connection = $connection;
    $this->dbPrefix = $dbPrefix;
  }

  public function create()
  {
  }
}
