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
        'label' => 'API Key',
        'max_length' => 255,
      ])
      ->add('switch', SwitchType::class, [
        'label' => 'Environment',
        'choices' => [
          'PROD' => true,
          'TEST' => false,
        ],
      ]);
  }
}
