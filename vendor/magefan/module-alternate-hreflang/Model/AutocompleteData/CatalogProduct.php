<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\AutocompleteData;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class CatalogProduct
 */
class CatalogProduct
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * CatalogProduct constructor.
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param $search
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItems($search)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter(
                array(
                    array('attribute'=> 'entity_id','eq' => $search),
                    array('attribute'=> 'name','like' => '%' . $search . '%'),
        ));

        $result = [];
        foreach ($collection as $item) {
            $result[] = [
                'value' => $item->getId() . '. ' . $item->getName(),
                'label' => $item->getId() . '. ' . $item->getName()
            ];
        }

        return $result;
    }

}