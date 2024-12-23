<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang\CollectionFactory as AlternateHreflangCollectionFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\App\Emulation;
use Magefan\AlternateHreflang\Api\BlogFactoryInterface;
use Magefan\AlternateHreflang\Api\AlternateHreflangUrlsInterface;

abstract class AbstractAlternateHreflang implements AlternateHreflangUrlsInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var AlternateHreflangCollectionFactory
     */
    protected $alternateHreflangCollectionFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var BlogFactoryInterface
     */
    protected $blogFactory;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * AbstractAlternateHreflang constructor.
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory
     * @param Registry $coreRegistry
     * @param BlogFactoryInterface $blogFactory
     * @param Emulation $emulation
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        Config $config,
        AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory,
        Registry $coreRegistry,
        BlogFactoryInterface $blogFactory,
        Emulation $emulation
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->alternateHreflangCollectionFactory = $alternateHreflangCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->blogFactory = $blogFactory;
        $this->emulation = $emulation;
    }

    /**
     * @param $id
     * @param $storeId
     * @return string
     */
    abstract public function getObjectUrl($id, $storeId);

    /**
     * @return string
     */
    abstract protected function getObjectType();

    /**
     * @param $currentObject
     * @return array
     */
    public function getAlternateUrls($currentObject)
    {
        $urls = [];

        if (!$currentObject) {
            return $urls;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $url = $this->getObjectUrl($currentObject->getId(), $storeId);
        if ($url) {
            $languageCode = $this->config->getLocaleCode($storeId);
            $urls[$languageCode] = $url;
        }

        $typeId = $this->config->getPageTypeId($this->getObjectType());
        $collection = $this->alternateHreflangCollectionFactory->create()
            ->addFieldToFilter('parent_id', $currentObject->getId())
            ->addFieldToFilter('type', $typeId);

        foreach ($collection as $item) {
            $localization = json_decode($item->getLocalization(), true);

            foreach ($localization as $storeId => $childObjectId) {
                $store = $this->storeManager->getStore($storeId);

                if (!$store->isActive()) {
                    continue;
                }

                if (!$this->isAvailableStoreGroup($store)) {
                    continue;
                }

                $languageCode = $this->config->getLocaleCode($storeId);
                if (empty($urls[$languageCode])) {
                    $url = $this->getObjectUrl($childObjectId, $storeId);
                    if ($url) {
                        $urls[$languageCode] = $url;
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * @param $store
     * @return bool
     */
    public function isAvailableStoreGroup($store)
    {
        if (!$this->config->isEnabled($store->getId())) {
            return false;
        }

        if (!$this->config->getDisplayHreflangTagsFor($this->getObjectType(), $store->getId())) {
            return false;
        }

        $currentStore = $this->storeManager->getStore();
        $currentStoreGroup = $this->config->getGroup($currentStore->getId());
        $storeGroup = $this->config->getGroup($store->getId());

        return ($currentStoreGroup && $storeGroup && $currentStoreGroup == $storeGroup);
    }
}
