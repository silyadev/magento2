<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\CatalogInventory\Model\Stock;

/**
 * Delete Legacy cataloginventory_stock_status by plain MySql query
 * Use for skip delete by \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::delete
 */
class DeleteCatalogInventoryStockStatusByDefaultSourceItem implements
    DeleteCatalogInventoryStockStatusByDefaultSourceItemInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductIdLocatorInterface
     */
    private $idLocator;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param ProductIdLocatorInterface $idLocator
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductIdLocatorInterface $idLocator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->idLocator = $idLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(SourceItemInterface $sourceItem)
    {
        $productIds = $this->idLocator->retrieveProductIdsBySkus([$sourceItem->getSku()]);
        $productId = isset($productIds[$sourceItem->getSku()]) ? key($productIds[$sourceItem->getSku()]) : false;

        if ($productId) {
            $connection = $this->resourceConnection->getConnection();
            $connection->delete(
                $connection->getTableName('cataloginventory_stock_status'),
                [
                    StockStatusInterface::PRODUCT_ID . ' = ?' => $productId,
                    StockItemInterface::STOCK_ID . ' = ?' => Stock::DEFAULT_STOCK_ID,
                    Stock::WEBSITE_ID . ' = ?' => 0
                ]
            );
        }
    }
}
