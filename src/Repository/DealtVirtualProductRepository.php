<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use Doctrine\DBAL\Connection;
use DealtModule\Tools\Helpers;
use PrestaShop\PrestaShop\Core\Domain\Product\Stock\ValueObject\OutOfStockType;
use Product;
use Category;
use StockAvailable;


/**
 * Repository class to help interacting with
 * the virtual products created by the dealtmodule
 */
class DealtVirtualProductRepository
{
  /**
   * @var Connection
   */
  private $connection;

  /**
   * @var string
   */
  private $dbPrefix;

  /**
   * LinkBlockRepository constructor.
   *
   * @param Connection $connection
   * @param string $dbPrefix
   */
  public function __construct(
    Connection $connection,
    string $dbPrefix
  ) {
    $this->connection = $connection;
    $this->dbPrefix = $dbPrefix;
  }


  /**
   * @param integer $id
   * @return Product
   */
  public function findOneById(int $id)
  {
    $product = new Product($id);
    return $product;
  }

  /**
   * Create the Dealt mission virtual product
   * 
   * @param string $missionTitle
   * @param string $dealtMissionId
   * @param string $missionPrice
   * 
   * @return Product
   */
  public function create(string $missionTitle, string $dealtMissionId, string $missionPrice)
  {
    $category = Category::searchByName(null, '__dealt__', true);
    $categoryId = $category['id_category'];

    $product = new Product();
    $product->reference = $dealtMissionId . '-dealt-product';
    $product->name = Helpers::createMultiLangField($missionTitle);
    $product->meta_description = '';
    $product->visibility = 'none'; // we want to hide from the public catalog
    $product->id_category_default = $categoryId;
    $product->price = Helpers::formatPrice($missionPrice);
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

    return $product;
  }

  /**
   * Updates the underlying product
   *
   * @param int $productId
   * @param string $missionTitle
   * @param string $missionPrice
   * 
   * @return Product
   */
  public function update(int $productId, string $missionTitle, string $missionPrice)
  {
    $product = new Product($productId);
    $product->name  = Helpers::createMultiLangField($missionTitle);
    $product->price = Helpers::formatPrice($missionPrice);
    $product->save();

    return $product;
  }
}
