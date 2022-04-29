<?php

declare(strict_types=1);

namespace DealtModule\Service;

use Dealt\DealtSDK\DealtClient;
use Dealt\DealtSDK\DealtEnvironment;

/**
 * Dealt API class wrapping the DealtSDK library
 */
final class DealtAPIService
{
  /** @var DealtClient */
  protected $client;

  /** @var string */
  protected $api_key;

  /** @var bool */
  protected $production;


  /**
   * @param string $api_key
   * @param string $production
   */
  public function __construct($api_key, $production)
  {
    $this->api_key = $api_key;
    $this->production = filter_var($production, FILTER_VALIDATE_BOOLEAN);
  }

  /**
   * Retrieves the DealtSDK\DealtClient or instantiates
   * a fresh instance on first call
   * 
   * @return DealtClient
   */
  public function getClient()
  {
    if (isset($this->client) && $this->client instanceof DealtClient) return $this->client;

    return new DealtClient([
      "api_key" => $this->api_key,
      "env" => $this->production ? DealtEnvironment::PRODUCTION : DealtEnvironment::TEST
    ]);
  }
}
