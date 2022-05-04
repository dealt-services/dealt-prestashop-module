<?php

namespace DealtModule\Forms\Admin;

use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\MoneyWithSuffixType;
use PrestaShopBundle\Form\Admin\Type\TextWithLengthCounterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DealtOfferFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
      ->add('id_offer', HiddenType::class)
      ->add('id_dealt_product', HiddenType::class)
      ->add('title_offer', TextType::class, [
        'label' => 'Title',
        'required' => true,
        'help' => "Will also be used as the virtual product's title",
        'translation_domain' => 'Modules.DealtModule.Admin',
      ])
      ->add('dealt_id_offer', TextWithLengthCounterType::class, [
        'label' => 'Offer UUID',
        'required' => true,
        'max_length' => 36,
        'position' => 'after',
        'translation_domain' => 'Modules.DealtModule.Admin',
      ])
      ->add('offer_price', MoneyWithSuffixType::class, [
        'label' => 'Offer price',
        'currency' => 'EUR',
        'suffix' => '(tax excl.)',
        'help' => 'Tax excluded',
        'translation_domain' => 'Modules.DealtModule.Admin',
      ])
      ->add('ids_category', CategoryChoiceTreeType::class, [
        'multiple' => true,
        'required' => false,
        'label' => 'Categories',
        'disabled_values' => [], // __dealt__ internal category not visible by default
        'translation_domain' => 'Modules.DealtModule.Admin',
      ]);
    }
}
