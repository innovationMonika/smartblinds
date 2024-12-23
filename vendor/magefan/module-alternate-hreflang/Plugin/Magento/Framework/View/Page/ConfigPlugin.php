<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Plugin\Magento\Framework\View\Page;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magefan\AlternateHreflang\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Change html lang tag
 */
class ConfigPlugin
{
    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ConfigPlugin constructor.
     * @param ResolverInterface $localeResolver
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResolverInterface $localeResolver,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @param PageConfig $subject
     * @param $elementType
     * @param $attribute
     * @param $value
     * @return array|void
     */
    public function beforeSetElementAttribute(PageConfig $subject, $elementType, $attribute, $value)
    {
        if ($elementType == 'html' && $attribute == 'lang') {
            // check if module is enabled
            if (!$this->config->isEnabled()) {
                return;
            }

            $storeId = $this->storeManager->getStore()->getId();
            $value = $this->config->getLocaleCode($storeId);

            return [$elementType, $attribute, $value];
        }
    }
}
