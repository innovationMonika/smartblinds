<?php declare(strict_types=1);

namespace GoMage\CatalogDiscountLabels\Model\ResourceModel;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class MinimalPrice
{
    private Category $categoryResource;
    private StoreManagerInterface $storeManager;
    private Product $productResource;

    public function __construct(
        Category $categoryResource,
        StoreManagerInterface $storeManager,
        Product $productResource
    ) {
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $this->productResource = $productResource;
    }

    public function loadTaxClassIdWithPrice($categoryId)
    {
        $priceAttribute = $this->productResource->getAttribute('smartblinds_price');
        $statusAttribute = $this->productResource->getAttribute('status');
        $taxClassIdAttribute = $this->productResource->getAttribute('tax_class_id');

        $entityIdField = $this->productResource->getEntityIdField();
        $linkField = $this->productResource->getLinkField();

        $connection = $this->categoryResource->getConnection();
        $select = $connection
            ->select()
            ->from(['cp' => 'catalog_category_product_index_store' . $this->storeManager->getStore()->getId()], [])
            ->joinInner(
                ['p' => $this->productResource->getEntityTable()],
                "cp.product_id = p.$entityIdField",
                []
            )
            ->joinLeft(
                ['s' => $statusAttribute->getBackendTable()],
                implode(' AND ', [
                    "s.$linkField = p.$linkField",
                    $connection->quoteInto('s.attribute_id = ?', $statusAttribute->getId()),
                    $connection->quoteInto('s.store_id = ?', $this->storeManager->getStore()->getId())
                ]),
                []
            )
            ->joinLeft(
                ['sd' => $statusAttribute->getBackendTable()],
                implode(' AND ', [
                    "sd.$linkField = p.$linkField",
                    $connection->quoteInto('sd.attribute_id = ?', $statusAttribute->getId()),
                    $connection->quoteInto('sd.store_id = ?', Store::DEFAULT_STORE_ID)
                ]),
                []
            )
            ->joinLeft(
                ['t' => $taxClassIdAttribute->getBackendTable()],
                implode(' AND ', [
                    "t.$linkField = p.$linkField",
                    $connection->quoteInto('t.attribute_id = ?', $taxClassIdAttribute->getId()),
                    $connection->quoteInto('t.store_id = ?', $this->storeManager->getStore()->getId())
                ]),
                ['tax_class_id' => 't.value']
            )
            ->joinLeft(
                ['td' => $taxClassIdAttribute->getBackendTable()],
                implode(' AND ', [
                    "td.$linkField = p.$linkField",
                    $connection->quoteInto('td.attribute_id = ?', $taxClassIdAttribute->getId()),
                    $connection->quoteInto('td.store_id = ?', Store::DEFAULT_STORE_ID)
                ]),
                ['default_tax_class_id' => 'td.value']
            )
            ->joinLeft(
                ['pr' => $priceAttribute->getBackendTable()],
                implode(' AND ', [
                    "pr.$linkField = p.$linkField",
                    $connection->quoteInto('pr.attribute_id = ?', $priceAttribute->getId()),
                    $connection->quoteInto('pr.store_id = ?', $this->storeManager->getStore()->getId())
                ]),
                ['price' => 'pr.value']
            )
            ->joinLeft(
                ['prd' => $priceAttribute->getBackendTable()],
                implode(' AND ', [
                    "prd.$linkField = p.$linkField",
                    $connection->quoteInto('prd.attribute_id = ?', $priceAttribute->getId()),
                    $connection->quoteInto('prd.store_id = ?', Store::DEFAULT_STORE_ID)
                ]),
                ['default_price' => 'prd.value']
            )
            ->where('cp.category_id = ?', $categoryId)
            ->where('cp.visibility != ?', Visibility::VISIBILITY_NOT_VISIBLE)
            ->where(new \Zend_Db_Expr('IFNULL(pr.value, prd.value) > ' . 0))
            ->where(new \Zend_Db_Expr('IFNULL(s.value, sd.value) = ' . Status::STATUS_ENABLED))
            ->order('pr.value ASC')
            ->order('prd.value ASC');
        $rows = $connection->fetchAll($select);
        /*$row = reset($rows);*/
        if (empty($rows)) {
            return 0;
        }
        $price = 999999;
        foreach ($rows as $row) {
            $taxClassId = (float) ($row['tax_class_id'] ?: $row['default_tax_class_id']);
            $price = min(((float) ($row['price'] ?: $row['default_price'])), $price);
        }

        return [$taxClassId, $price];
    }
}
