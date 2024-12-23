<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

use Magento\Cms\Helper\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class provices methods to get module configuration properties
 */
class Config
{
    /**
     * Path to extension status
     */
    const EXTENSION_ENABLED = 'alternatehreflang/general/enabled';

    /**
     * Path to hreflang tags status
     */
    const DISPLAY_HREFLANG_TAGS_FOR = 'alternatehreflang/general/use_hreflang_tag_for';

    /**
     * Path to display for NOINDEX pages status
     */
    const DISPLAY_FOR_NOINDEX_ENABLED = 'alternatehreflang/general/display_for_noindex';

    /**
     * Path to locale code
     */
    const LOCALE_CODE = 'general/locale/code';

    /**
     * Path to product use categories
     */
    const PRODUCT_USE_CATEGORIES = 'catalog/seo/product_use_categories';

    /**
     * Path to locale depends on region status
     */
    const LOCALE_DEPENDS_ON_REGION = 'alternatehreflang/locale_options/depends_on_region';

    /**
     * Path to store group options
     */
    const STORE_GROUP = 'alternatehreflang/locale_options/group';

    /**
     * Path to x-default value store id
     */
    const XDEFAULT_STORE = 'alternatehreflang/locale_options/xdefault_store';

    /**
     * Path to add store code to urls status
     */
    const STORE_CODE_ENABLED = 'web/url/use_store';

    /**
     * Path to add custom locale code
     */
    const CUSTOM_LOCALE_CODE = 'alternatehreflang/locale_options/locale';

    /**
     * Get hreflang tags
     */
    const PAGE_TITLE_NONE = 'none';
    const PAGE_TITLE_HOMEPAGE = 'homepage';
    const PAGE_TITLE_PRODUCT = 'product';
    const PAGE_TITLE_CATEGORY = 'category';
    const PAGE_TITLE_CMS = 'cms';
    const PAGE_TITLE_BLOG_POST = 'blog_post';
    const PAGE_TITLE_BLOG_CATEGORY = 'blog_category';
    const PAGE_TITLE_BLOG_INDEX = 'blog_index';
    const PAGE_TITLE_BLOG_TAG = 'blog_tag';
    const PAGE_TITLE_BLOG_AUTHOR = 'blog_author';
    const PAGE_TITLE_SECONDBLOG_POST = 'secondblog_post';
    const PAGE_TITLE_SECONDBLOG_CATEGORY = 'secondblog_category';
    const PAGE_TITLE_SECONDBLOG_INDEX = 'secondblog_index';
    const PAGE_TITLE_SECONDBLOG_TAG = 'secondblog_tag';
    const PAGE_TITLE_SECONDBLOG_AUTHOR = 'secondblog_author';

    /**
     * Page type
     */
    const BLOG_POST_TYPE = 1;
    const BLOG_CATEGORY_TYPE = 2;
    const STATIC_PAGE_TYPE = 3;
    const CATALOG_PRODUCT_TYPE = 4;
    const CATALOG_CATEGORY_TYPE = 5;
    const BLOG_TAG_TYPE = 6;
    const BLOG_AUTHOR_TYPE = 7;
    const SECONDBLOG_POST_TYPE = 8;
    const SECONDBLOG_CATEGORY_TYPE = 9;
    const SECONDBLOG_TAG_TYPE = 10;
    const SECONDBLOG_AUTHOR_TYPE = 11;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ResolverInterface $localeResolver
     * @param Http $request
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResolverInterface $localeResolver,
        Http $request,
        ModuleManager $moduleManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param $type
     * @return int
     */
    public function getPageTypeId($type)
    {
        switch ($type) {
            case self::PAGE_TITLE_BLOG_POST:
                return self::BLOG_POST_TYPE;
            case self::PAGE_TITLE_BLOG_CATEGORY:
                return self::BLOG_CATEGORY_TYPE;
            case self::PAGE_TITLE_BLOG_TAG:
                return self::BLOG_TAG_TYPE;
            case self::PAGE_TITLE_BLOG_AUTHOR:
                return self::BLOG_AUTHOR_TYPE;
            case self::PAGE_TITLE_SECONDBLOG_POST:
                return self::SECONDBLOG_POST_TYPE;
            case self::PAGE_TITLE_SECONDBLOG_CATEGORY:
                return self::SECONDBLOG_CATEGORY_TYPE;
            case self::PAGE_TITLE_SECONDBLOG_TAG:
                return self::SECONDBLOG_TAG_TYPE;
            case self::PAGE_TITLE_SECONDBLOG_AUTHOR:
                return self::SECONDBLOG_AUTHOR_TYPE;
            case self::PAGE_TITLE_CMS:
                return self::STATIC_PAGE_TYPE;
            case self::PAGE_TITLE_PRODUCT:
                return self::CATALOG_PRODUCT_TYPE;
            case self::PAGE_TITLE_CATEGORY:
                return self::CATALOG_CATEGORY_TYPE;
        }
    }

    /**
     * Retrieve true if module is enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfigValue(self::EXTENSION_ENABLED, $storeId);
    }

    /**
     * @param $storeId
     * @param $blogType
     * @return bool
     */
    public function isBlogPermalinkEnabled($storeId = null, $blogType = '')
    {
        return !$this->getConfigValue(
            'mf' . $blogType . 'blog/advanced_permalink/enabled',
            $storeId
        ) && $this->getConfigValue(
            'mf' . $blogType . 'blog/general/enabled',
            $storeId
        );
    }

    /**
     * @param $storeId
     * @param $blogType
     * @return bool
     */
    public function isBlogPlusPermalinkEnabled($storeId = null, $blogType = '')
    {
        return $this->getConfigValue(
            'mf' . $blogType . 'blog/advanced_permalink/enabled',
            $storeId
        ) && $this->getConfigValue(
            'mf' . $blogType . 'blog/general/enabled',
            $storeId
        );
    }

    /**
     * @param $pageType
     * @param null $storeId
     * @return bool
     */
    public function getDisplayHreflangTagsFor($pageType, $storeId = null)
    {
        $displayFor = explode(',', $this->getConfigValue(self::DISPLAY_HREFLANG_TAGS_FOR, $storeId));
        $pageTypeIsEnabled = in_array($pageType, $displayFor);

        if ($pageType === 'blog_author') {
            $pageTypeIsEnabled = $pageTypeIsEnabled && $this->moduleManager->isEnabled('Magefan_BlogAuthor');
        } elseif ($pageType === 'secondblog_author') {
            $pageTypeIsEnabled = $pageTypeIsEnabled && $this->moduleManager->isEnabled('Magefan_SecondBlogAuthor');
        }

        return in_array(self::PAGE_TITLE_NONE, $displayFor) ? false : $pageTypeIsEnabled;
    }

    /**
     * Retrieve true if Display For NOINDEX is enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function isDisplayForNoindexEnabled($storeId = null)
    {
        return (bool)$this->getConfigValue(self::DISPLAY_FOR_NOINDEX_ENABLED, $storeId);
    }

    /**
     * Retrieve locale code
     *
     * @param null $storeId
     * @return mixed
     */
    public function getLocaleCode($storeId = null)
    {
        $languageCode = $this->getConfigValue(self::LOCALE_CODE, $storeId);

        if (!$this->isLocaleDependsOnRegion($storeId)) {
            $languageInfo = explode('_', $languageCode);
            $languageCode = $languageInfo[0];
        } elseif ($customLocaleCode = $this->getCustomLocaleCode($storeId)) {
            $languageCode = $customLocaleCode;
        }

        $languageCode = str_replace('_', '-', $languageCode);

        return $languageCode;
    }

    /**
     * Retrieve true if product include a category in URL
     *
     * @param null $storeId
     * @return bool
     */
    public function isProductUseCategoriesPath($storeId = null)
    {
        return (bool)$this->getConfigValue(self::PRODUCT_USE_CATEGORIES, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isLocaleDependsOnRegion($storeId)
    {
        return $this->getConfigValue(self::LOCALE_DEPENDS_ON_REGION, $storeId);
    }

    /**
     * @return string
     * @deprecated Add $pageType property to class
     */
    public function getPageType()
    {
        switch ($this->request->getFullActionName()) {
            case 'cms_index_index':
                return self::PAGE_TITLE_HOMEPAGE;
            case 'catalog_product_view':
                return self::PAGE_TITLE_PRODUCT;
            case 'catalog_category_view':
                return self::PAGE_TITLE_CATEGORY;
            case 'cms_page_view':
                return self::PAGE_TITLE_CMS;
            case 'blog_index_index':
                return self::PAGE_TITLE_BLOG_INDEX;
            case 'blog_post_view':
                return self::PAGE_TITLE_BLOG_POST;
            case 'blog_category_view':
                return self::PAGE_TITLE_BLOG_CATEGORY;
            case 'blog_tag_view':
                return self::PAGE_TITLE_BLOG_TAG;
            case 'blog_author_view':
                return self::PAGE_TITLE_BLOG_AUTHOR;
            case 'secondblog_index_index':
                return self::PAGE_TITLE_SECONDBLOG_INDEX;
            case 'secondblog_post_view':
                return self::PAGE_TITLE_SECONDBLOG_POST;
            case 'secondblog_category_view':
                return self::PAGE_TITLE_SECONDBLOG_CATEGORY;
            case 'secondblog_tag_view':
                return self::PAGE_TITLE_BLOG_TAG;
            case 'secondblog_author_view':
                return self::PAGE_TITLE_SECONDBLOG_AUTHOR;
        }
    }

    /**
     * Retrieve store group
     *
     * @param $storeId
     * @return int
     */
    public function getGroup($storeId)
    {
        return $this->getConfigValue(self::STORE_GROUP, $storeId);
    }

    /**
     * Retrieve x-default store id
     *
     * @param $storeId
     * @return int
     */
    public function getXDefaultStoreId($storeId = null)
    {
        return (int)$this->getConfigValue(self::XDEFAULT_STORE, $storeId);
    }

    /**
     * @return bool
     */
    public function isStoreCodeInUrlEnabled()
    {
        return (bool)$this->getConfigValue(self::STORE_CODE_ENABLED);
    }

    /**
     * @return string
     */
    public function getCmsIndex()
    {
        return (string)$this->getConfigValue(Page::XML_PATH_HOME_PAGE);
    }

    /**
     * @param $storeId
     *
     * @return string
     */
    public function getCustomLocaleCode($storeId)
    {
        return (string)$this->getConfigValue(self::CUSTOM_LOCALE_CODE, $storeId);
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
