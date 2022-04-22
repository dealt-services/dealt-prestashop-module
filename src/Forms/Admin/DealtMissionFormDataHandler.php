<?php

namespace DealtModule\Forms\Admin;

use DealtModule\Entity\DealtMission;
use Doctrine\ORM\EntityManagerInterface;

use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;
use Product;
use Category;
use Language;
use PrestaShop\PrestaShop\Core\Domain\Product\Stock\ValueObject\OutOfStockType;
use StockAvailable;
use Tools;

class DealtMissionFormDataHandler implements FormDataHandlerInterface
{

  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  /**
   * @param EntityManagerInterface $entityManager
   */
  public function __construct(
    EntityManagerInterface $entityManager
  ) {
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $data)
  {
    $mission = new DealtMission();
    $mission->setMissionTitle($data['title_mission']);
    $mission->setDealtMissionId($data['dealt_id_mission']);
    /* automatically create virtual dealt product for mission */
    $mission->setDealtVirtualProductId($this->createDealtVirtualProduct($data['title_mission'], $data['dealt_id_mission'], $data['misison_price']));
    $mission->updateTimestamps();

    $this->entityManager->persist($mission);
    $this->entityManager->flush();

    return $mission->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function update($id, array $data)
  {
  }

  /**
   * Dynamically create the Dealt mission virtual product
   * 
   * @param string $missionTitle
   * @param string $dealtMissionId
   * @param string $missionPrice
   * @return int
   */
  private function createDealtVirtualProduct(string $missionTitle, string $dealtMissionId, string $missionPrice)
  {
    $category = Category::searchByName(null, '__dealt__', true);
    $categoryId = $category['id_category'];

    $product = new Product();
    $product->reference = $dealtMissionId . '-dealt-product';
    $product->name = $this->createMultiLangField($missionTitle);
    $product->meta_description = '';
    $product->visibility = 'none'; // we want to hide from the public catalog
    $product->id_category_default = $categoryId;
    $product->price = $missionPrice;
    $product->minimal_quantity = 1;
    $product->show_price = 1;
    $product->on_sale = 0;
    $product->online_only = 1;
    $product->is_virtual = 1;

    $product->add();

    /* set stock available even when quantity = 0 */
    $stockAvailable = new StockAvailable($product->id);
    $stockAvailable->out_of_stock = OutOfStockType::OUT_OF_STOCK_AVAILABLE;
    $stockAvailable->update();

    return $product->id;
  }

  /**
   * Helper function to create multilang string
   * TODO: should be moved to custom Tools service
   * 
   * @param [type] $field
   * @return mixed
   */
  private function createMultiLangField($field)
  {
    $res = [];
    foreach (Language::getLanguages() as $lang) {
      $res[$lang['id_lang']] = $field;
    }

    return $res;
  }
}
