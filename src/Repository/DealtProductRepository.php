<?php

declare(strict_types=1);

namespace DealtModule\Repository;

use Category;
use Context;
use DealtModule\Database\DealtInstaller;
use DealtModule\Tools\Helpers;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Core\Domain\Product\Stock\ValueObject\OutOfStockType;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use Product;
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
     * ⚠️ The ProductRepository is not available in the FRONT PS Service container
     *
     * @var ProductRepository|null
     */
    private $psProductRepository;

    /**
     * @param ProductRepository $psProductRepository
     */
    public function __construct(
        $psProductRepository
    ) {
        $this->psProductRepository = $psProductRepository;
    }

    /**
     * @param int $productId
     *
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
        $category = Category::searchByName(Context::getContext()->language->id, DealtInstaller::$DEALT_PRODUCT_CATEGORY_NAME, true);
        $categoryId = $category['id_category'];

        $product = new Product();
        $product->reference = $dealtOfferId . '-dealt-product';
        $product->name = Helpers::createMultiLangField($offerTitle);
        $product->meta_description = '';
        $product->visibility = 'none'; /* we want to hide from the public catalog */
        $product->id_category_default = $categoryId;
        $product->price = Helpers::formatPriceForDB($offerPrice);
        $product->minimal_quantity = 1;
        $product->show_price = true;
        $product->on_sale = false;
        $product->online_only = true;
        $product->is_virtual = true;

        $product->add();

        /* set stock available even when quantity = 0 */
        $stockAvailable = new StockAvailable(StockAvailable::getStockAvailableIdByProductId($product->id));
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
     *
     * @return Product
     */
    public function update(int $productId, string $offerTitle, string $offerPrice)
    {
        $product = $this->findOneById($productId);
        $product->name = Helpers::createMultiLangField($offerTitle);
        $product->price = Helpers::formatPriceForDB($offerPrice);
        $product->save();

        return $product;
    }
}
