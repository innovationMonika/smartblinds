<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\AlternateHreflang;

use Magefan\AlternateHreflang\Model\AbstractAlternateHreflang;
use Magefan\AlternateHreflang\Model\BlogFactory;
use Magefan\AlternateHreflang\Model\Config;
use Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang\CollectionFactory as AlternateHreflangCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryRepository;

class Category extends AbstractAlternateHreflang
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * Category constructor.
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory
     * @param Registry $coreRegistry
     * @param BlogFactory $blogFactory
     * @param Emulation $emulation
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        Config $config,
        AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory,
        Registry $coreRegistry,
        BlogFactory $blogFactory,
        Emulation $emulation,
        CategoryRepository $categoryRepository
    ) {
        parent::__construct($request, $storeManager, $config, $alternateHreflangCollectionFactory, $coreRegistry, $blogFactory, $emulation);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param $id
     * @param $storeId
     * @return string
     */
    public function getObjectUrl($id, $storeId)
    {
        $currentStoreId = $this->storeManager->getStore()->getId();
        $categoryId = $id;

        // start environment emulation
        $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        try {
            $category = $this->categoryRepository->get($categoryId, $storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->emulation->stopEnvironmentEmulation();
            return false;
        }

        $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        if ($category->getIsActive() && in_array($rootCategoryId, $category->getPathIds())) {
            $url = str_replace(
                $this->storeManager->getStore($currentStoreId)->getBaseUrl(),
                $this->storeManager->getStore($storeId)->getBaseUrl(),
                $this->categoryRepository->get($categoryId, $storeId)->getUrl()
            );
        } else {
            $url = false;
        }

        // stop environment emulation
        $this->emulation->stopEnvironmentEmulation();

        return $url;
    }

    /**
     * @return string
     */
    protected function getObjectType()
    {
        return Config::PAGE_TITLE_CATEGORY;
    }

    /**
     * @param $currentObject
     * @return array
     */
    public function getAlternateUrls($currentObject)
    {
        if (!$currentObject) {
            return [];
        }

        $urls = parent::getAlternateUrls($currentObject);

        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->isActive()) {
                continue;
            }
            if (!$this->isAvailableStoreGroup($store)) {
                continue;
            }
            $storeId = $store->getId();
            $languageCode = $this->config->getLocaleCode($storeId);
            if (empty($urls[$languageCode])) {
                $url = $this->getObjectUrl($currentObject->getId(), $storeId);

                if ($url) {
                    $urls[$languageCode] = $url;
                }
            }
        }

        return $urls;
    }
}
