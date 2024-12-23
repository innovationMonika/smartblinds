<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Block\AlternateHreflang;

use Magefan\AlternateHreflang\Api\GetAlternateHreflangInterface;
use Magefan\AlternateHreflang\Block\AlternateHreflang;
use Magefan\AlternateHreflang\Model\Config;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang\CollectionFactory
    as AlternateHreflangCollectionFactory;
use Magento\Cms\Model\Page as CmsPage;

/**
 * Class Cms Page Alternate Hreflang
 */
class Cms extends AlternateHreflang
{
    /**
     * @var CmsPage
     */
    private $cmsPage;

    /**
     * Cms constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory
     * @param GetAlternateHreflangInterface $getAlternateHreflang
     * @param CmsPage $cmsPage
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Config $config,
        StoreManagerInterface $storeManager,
        AlternateHreflangCollectionFactory $alternateHreflangCollectionFactory,
        GetAlternateHreflangInterface $getAlternateHreflang,
        CmsPage $cmsPage,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry, $config, $storeManager, $alternateHreflangCollectionFactory, $getAlternateHreflang, $data);
        $this->cmsPage = $cmsPage;
    }

    /**
     * @return CmsPage
     */
    public function getCurrentObject()
    {
        return $this->cmsPage;
    }

    /**
     * @return string
     */
    protected function getObjectType()
    {
        if ('cms_index_index' == $this->getRequest()->getFullActionName()) {
            return Config::PAGE_TITLE_HOMEPAGE;
        } else {
            return Config::PAGE_TITLE_CMS;
        }
    }
}
