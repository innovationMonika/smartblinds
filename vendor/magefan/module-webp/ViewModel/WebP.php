<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\WebP\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class WebpP
 * @package Magefan\WebP\ViewModel
 */
class WebP implements ArgumentInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * WebpP constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getStoreUrls():array
    {
        $store = $this->storeManager->getStore();

        return [
            'base' => $store->getBaseUrl(UrlInterface::URL_TYPE_WEB),
            'media' => $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            'static' => preg_replace('/version\d{10}\//i', '', $store->getBaseUrl(UrlInterface::URL_TYPE_STATIC))
        ];
    }
}
