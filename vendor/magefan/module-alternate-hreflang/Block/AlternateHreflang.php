<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Framework\Registry;
use Magefan\AlternateHreflang\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang\CollectionFactory
    as AlternateHreflangCollectionFactory;
use Magefan\AlternateHreflang\Api\GetAlternateHreflangInterface;

/**
 * Class AlternateHreflang
 */
abstract class AlternateHreflang extends AbstractBlock
{
    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AlternateHreflangCollectionFactory
     */
    protected $alternateHreflangCollectionFactory;

    /**
     * @var GetAlternateHreflangInterface
     */
    protected $getAlternateHreflang;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * AlternateHreflang constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory
     * @param GetAlternateHreflangInterface $getAlternateHreflang
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Config $config,
        StoreManagerInterface $storeManager,
        AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory,
        GetAlternateHreflangInterface $getAlternateHreflang,
        array $data = [],
        \Magento\Framework\View\Page\Config $pageConfig = null
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->alternateHreflangCollectionFactory = $alternateHreflangCollectionFactory;
        $this->getAlternateHreflang = $getAlternateHreflang;

        $this->pageConfig = $pageConfig ?: $objectManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\View\Page\Config::class);
    }

    /**
     * @return object
     */
    abstract public function getCurrentObject();

    /**
     * @return string
     */
    abstract protected function getObjectType();

    /**
     * @return array
     */
    public function getAlternateUrls()
    {
        return $this->getAlternateHreflang->execute($this->getCurrentObject(), $this->getObjectType());
    }

    /**
     * Render html output
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        if (!$this->config->isDisplayForNoindexEnabled() && 0 === stripos($this->pageConfig->getRobots(), 'NOINDEX')) {
            return '';
        }

        // check if module and hreflang tags enabled
        if ($this->config->isEnabled()
            && $this->config->getDisplayHreflangTagsFor($this->getObjectType())
        ) {
            $html = '';
            $alternateUrls = $this->getAlternateUrls();

            asort($alternateUrls);

            foreach ($alternateUrls as $languageCode => $url) {
                $html .= PHP_EOL . '<link rel="alternate" hreflang="'
                    . $this->escapeHtml($languageCode)
                    . '" href="' . $url
                    . '" />';
            }

            if ($xDefaultStoreId = $this->config->getXDefaultStoreId()) {
                $languageCode = $this->config->getLocaleCode($xDefaultStoreId);

                if (isset($alternateUrls[$languageCode])) {
                    $url = $alternateUrls[$languageCode];
                    $html .= PHP_EOL . '<link rel="alternate" hreflang="x-default" href="'
                        . $url
                        . '" />';
                }
            }

            return $html;
        }

        return '';
    }
}
