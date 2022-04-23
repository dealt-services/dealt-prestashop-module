<?php

namespace DealtModule\Forms\Admin;

use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\TextWithLengthCounterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShopBundle\Form\Admin\Type\MoneyWithSuffixType;
use Symfony\Component\Form\FormBuilderInterface;

class DealtMissionFormType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('id_mission', HiddenType::class)
      ->add('id_dealt_virtual_product', HiddenType::class)
      ->add('title_mission', TextType::class, [
        'label' => 'Title',
        'required' => true,
        'help' => "Will also be used as the virtual product's title",
        'translation_domain' => 'Modules.DealtModule.Admin',
      ])
      ->add('dealt_id_mission', TextWithLengthCounterType::class, [
        'label' => 'Mission UUID',
        'required' => true,
        'max_length' => 36,
        'position' => 'after',
        'translation_domain' => 'Modules.DealtModule.Admin',
      ])
      ->add('mission_price', MoneyWithSuffixType::class, [
        'label' => 'Mission price',
        'currency' => 'EUR',
        'suffix' => '(tax excl.)',
        'help' => "Tax excluded",
        'translation_domain' => 'Modules.DealtModule.Admin',
      ])
      ->add('ids_category', CategoryChoiceTreeType::class, [
        'multiple' => true,
        'required' => false,
        'label' => 'Categories',
        'disabled_values' => [], // should disable __dealt__ internal category
        'translation_domain' => 'Modules.DealtModule.Admin',
      ]);
  }
}
