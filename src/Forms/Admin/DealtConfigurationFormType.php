<?php

namespace DealtModule\Forms\Admin;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DealtConfigurationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
      ->add('api_key', TextType::class, [
        'label' => 'API Key (uuid)',
        'translation_domain' => 'Modules.Dealtmodule.Admin',
      ])
      ->add('prod_env', SwitchType::class, [
        'label' => 'Environment',
        'translation_domain' => 'Modules.Dealtmodule.Admin',
        'choices' => [
          'Test' => false,
          'Production' => true,
        ],
      ]);
    }
}
