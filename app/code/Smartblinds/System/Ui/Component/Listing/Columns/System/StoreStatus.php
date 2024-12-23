<?php

namespace Smartblinds\System\Ui\Component\Listing\Columns\System;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class StoreStatus extends Column
{
    protected $storeRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreRepositoryInterface $storeRepository,
        array $components = [],
        array $data = []
    ) {
        $this->storeRepository = $storeRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function getStoreNameById($storeId)
    {
        try {
            $store = $this->storeRepository->getById($storeId);
            return ($store->getName() != 'Admin') ? $store->getName() : '';
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty($item['storeviews'])) {
                    
                    $storeIds = explode(",", $item['storeviews']);
                    $storeNames = [];
                    if (is_array($storeIds)) {
                        foreach ($storeIds as $storeId) {
                            $storeName = $this->getStoreNameById($storeId);
                            if ($storeName) {
                                $storeNames[] = $storeName;
                            }
                        }
                     $item['storeviews'] = implode(", ", $storeNames);
                    }
                }else{
                    $item['storeviews'] = "All Store Views";
                }
            }
        }

        return $dataSource;
    }
}
