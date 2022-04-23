<?php

namespace DealtModule\Forms\Admin;

use PrestaShopBundle\Form\Admin\Type\TextWithLengthCounterType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DealtConfigurationFormType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('api_key', TextWithLengthCounterType::class, [
        'label' => 'API Key (uuid)',
        'translation_domain' => 'Modules.DealtModule.Admin',
        'max_length' => 36,
      ])
      ->add('prod_env', SwitchType::class, [
        'label' => 'Environment',
        'translation_domain' => 'Modules.DealtModule.Admin',
        'choices' => [
          'Test/Staging' => false,
          'Production' => true,
        ],
      ]);
  }
}
