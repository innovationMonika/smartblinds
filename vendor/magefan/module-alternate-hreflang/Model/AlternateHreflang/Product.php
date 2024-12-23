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
use Magento\Catalog\Model\ProductRepository;

class Product extends AbstractAlternateHreflang
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * Product constructor.
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory
     * @param Registry $coreRegistry
     * @param BlogFactory $blogFactory
     * @param Emulation $emulation
     * @param ProductRepository $productRepository
     */
public function __construct(
    RequestInterface $request,
    StoreManagerInterface $storeManager,
    Config $config,
    AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory,
    Registry $coreRegistry,
    BlogFactory $blogFactory,
    Emulation $emulation,
    ProductRepository $productRepository
) {
    parent::__construct($request, $storeManager, $config, $alternateHreflangCollectionFactory, $coreRegistry, $blogFactory, $emulation);
    $this->productRepository = $productRepository;
}

    /**
     * @param $id
     * @param $storeId
     * @return string
     */
    public function getObjectUrl($id, $storeId)
    {
        $productId = $id;
        // start environment emulation
        $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

        try {
            $product = $this->productRepository->getById($productId, true, $storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->emulation->stopEnvironmentEmulation();
            return false;
        }

        if (!in_array($this->storeManager->getStore($storeId)->getWebsiteId(), $product->getWebsiteIds())) {
            $this->emulation->stopEnvironmentEmulation();
            return false;
        }

        if ($product->getVisibility() == 1 || $product->getStatus() == 2) {
            $this->emulation->stopEnvironmentEmulation();
            return false;
        }

        if ($this->config->isProductUseCategoriesPath()) {
            $productUrl = $product->getUrlModel()->getUrl($product, ['_scope' => $storeId]);
        } else {
            $productUrl = $product->getUrlModel()->getUrl($product, ['_ignore_category' => true, '_scope' => $storeId]);
        }
        // stop environment emulation
        $this->emulation->stopEnvironmentEmulation();

        return $productUrl;
    }

    /**
     * @return string
     */
    protected function getObjectType()
    {
        return Config::PAGE_TITLE_PRODUCT;
    }

    /**
     * @param $currentObject
     * @param null $pageType
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
