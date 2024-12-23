<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\AutocompleteData;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

/**
 * Class CmsPage
 * Provides Data for Autocomplete Ajax Call
 */
class CmsPage
{
    /**
     * @var CollectionFactory|null
     */
    private $collectionFactory = null;

    /**
     * Page constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $search
     * @return array
     */
    public function getItems($search)
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ['page_id', 'title'],
                [
                    ['eq' => $search],
                    ['like' => '%' . $search . '%'],
                ]
            );

        $result = [];
        foreach ($collection as $item) {
            $result[] = [
                'value' => $item->getId() . '. ' . $item->getTitle(),
                'label' => $item->getId() . '. ' . $item->getTitle()
            ];
        }

        return $result;
    }
}
