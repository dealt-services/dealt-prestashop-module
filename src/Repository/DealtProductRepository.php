<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use Doctrine\DBAL\Connection;
use DealtModule\Tools\Helpers;
use PrestaShop\PrestaShop\Core\Domain\Product\Stock\ValueObject\OutOfStockType;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use Product;
use Category;
use DealtModule\Database\DealtInstaller;
use StockAvailable;


/**
 * Repository class to help interacting with
 * the virtual products created by the dealtmodule
 * 
 * This Repository uses the legacy ObjectModel API and DBQuery class
 */
class DealtProductRepository
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
   * ⚠️ The ProductRepository is not available in the FRONT PS Service container
   * @var ProductRepository|null
   */
  private $psProductRepository;

  /**
   * LinkBlockRepository constructor.
   *
   * @param Connection $connection
   * @param string $dbPrefix
   */
  public function __construct(
    Connection $connection,
    string $dbPrefix,
    $psProductRepository
  ) {
    $this->connection = $connection;
    $this->dbPrefix = $dbPrefix;
    $this->psProductRepository = $psProductRepository;
  }


  /**
   * @param integer $productId
   * @return Product
   */
  public function findOneById(int $productId)
  {
    $product = $this->psProductRepository->get(new ProductId($productId));
    return $product;
  }

  /**
   * Create the Dealt offer virtual product
   * 
   * @param string $offerTitle
   * @param string $dealtOfferId
   * @param string $offerPrice
   * 
   * @return Product
   */
  public function create(string $offerTitle, string $dealtOfferId, string $offerPrice)
  {
    $category = Category::searchByName(null, DealtInstaller::$DEALT_PRODUCT_CATEGORY_NAME, true);
    $categoryId = $category['id_category'];

    $product = new Product();
    $product->reference = $dealtOfferId . '-dealt-product';
    $product->name = Helpers::createMultiLangField($offerTitle);
    $product->meta_description = '';
    $product->visibility = 'none'; // we want to hide from the public catalog
    $product->id_category_default = $categoryId;
    $product->price = Helpers::formatPrice($offerPrice);
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
   * @param string $offerTitle
   * @param string $offerPrice
   *
   * @throws CoreException
   * @return Product
   */
  public function update(int $productId, string $offerTitle, string $offerPrice)
  {
    $product = $this->findOneById($productId);
    $product->name  = Helpers::createMultiLangField($offerTitle);
    $product->price = Helpers::formatPrice($offerPrice);
    $product->save();

    return $product;
  }
}
