<?php

namespace Smartblinds\Framework\Plugin\View\Page\Config;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Store\Model\StoreManagerInterface;

class MediaSupport
{
    private const MEDIA_PREFIX = 'media::';

    private StoreManagerInterface $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function aroundAddPageAsset(
        PageConfig $subject,
        callable $proceed,
        $file,
        array $properties = [],
        $name = null
    ) {
        if (strpos($file, self::MEDIA_PREFIX) !== 0) {
            return $proceed($file, $properties, $name);
        }
        $filepath = str_replace(self::MEDIA_PREFIX, '', $file);
        return $subject->addRemotePageAsset(
            $this->storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $filepath,
            '',
            $properties
        );
    }
}
