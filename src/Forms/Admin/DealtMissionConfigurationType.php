<?php

namespace DealtModule\Forms\Admin;

use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DealtMissionConfigurationType extends AbstractType
{

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    // should disable __dealt__ internal category
    $builder
      ->add('category_id', CategoryChoiceTreeType::class, [
        'multiple' => true,
        'label' => 'Category choice type',
        'disabled_values' => []
      ]);
  }
}
