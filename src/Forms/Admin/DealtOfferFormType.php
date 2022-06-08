<?php

namespace DealtModule\Forms\Admin;

use Category;
use Context;
use DealtModule\Database\DealtInstaller;
use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\MoneyWithSuffixType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DealtOfferFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param mixed $options
     *
     * @return void
     */
    public function buildForm($builder, $options)
    {
        $dealtCategory = Category::searchByName(Context::getContext()->language->id, DealtInstaller::$DEALT_PRODUCT_CATEGORY_NAME, true, true);

        $builder
      ->add('id_offer', HiddenType::class)
      ->add('id_dealt_product', HiddenType::class)
      ->add('title_offer', TextType::class, [
        'label' => 'Title',
        'required' => true,
        'help' => "Will also be used as the virtual product's title",
        'translation_domain' => 'Modules.Dealtmodule.Admin',
      ])
      ->add('dealt_id_offer', TextType::class, [
        'label' => 'Offer UUID',
        'required' => true,
        'translation_domain' => 'Modules.Dealtmodule.Admin',
      ])
      ->add('offer_price', MoneyWithSuffixType::class, [
        'label' => 'Offer price',
        'currency' => 'EUR',
        'suffix' => '(tax excl.)',
        'help' => 'Tax excluded',
        'translation_domain' => 'Modules.Dealtmodule.Admin',
      ])
      ->add('ids_category', CategoryChoiceTreeType::class, [
        'multiple' => true,
        'required' => false,
        'label' => 'Categories',
        'disabled_values' => [$dealtCategory['id_category']],
        'translation_domain' => 'Modules.Dealtmodule.Admin',
      ]);
    }
}
