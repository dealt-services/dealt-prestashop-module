<?php

namespace DealtModule\Forms\Admin;

use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

final class DealtConfigurationFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        $apiKey = $this->configuration->get('DEALTMODULE_API_KEY');
        $prodEnv = $this->configuration->get('DEALTMODULE_PROD_ENV');

        return [
      'configuration' => [
        'api_key' => $apiKey ?? '',
        'prod_env' => $prodEnv,
      ],
    ];
    }

    public function setData(array $data)
    {
        $this->configuration->set('DEALTMODULE_API_KEY', $data['configuration']['api_key']);
        $this->configuration->set('DEALTMODULE_PROD_ENV', $data['configuration']['prod_env']);

        return [];
    }
}
