<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magefan\AlternateHreflang\Model\Config;

/**
 * Page type options
 */
class HreflangTags implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => Config::PAGE_TITLE_NONE, 'label' => __('Please Select')],
            ['value' => Config::PAGE_TITLE_HOMEPAGE, 'label' => __('Homepage')],
            ['value' => Config::PAGE_TITLE_CMS, 'label' => __('CMS Pages')],
            ['value' => Config::PAGE_TITLE_PRODUCT, 'label' => __('Product Pages')],
            ['value' => Config::PAGE_TITLE_CATEGORY, 'label' => __('Category Pages')],
            ['value' => Config::PAGE_TITLE_BLOG_INDEX, 'label' => __('Magefan Blog Home Page')],
            ['value' => Config::PAGE_TITLE_BLOG_POST, 'label' => __('Magefan Blog Post Pages')],
            ['value' => Config::PAGE_TITLE_BLOG_CATEGORY, 'label' => __('Magefan Blog Category Pages')],
            ['value' => Config::PAGE_TITLE_BLOG_TAG, 'label' => __('Magefan Blog Tag Pages')],
            ['value' => Config::PAGE_TITLE_BLOG_AUTHOR, 'label' => __('Magefan Blog Author Pages')],
            ['value' => Config::PAGE_TITLE_SECONDBLOG_INDEX, 'label' => __('Magefan SecondBlog Home Page')],
            ['value' => Config::PAGE_TITLE_SECONDBLOG_POST, 'label' => __('Magefan SecondBlog Post Pages')],
            ['value' => Config::PAGE_TITLE_SECONDBLOG_CATEGORY, 'label' => __('Magefan SecondBlog Category Pages')],
            ['value' => Config::PAGE_TITLE_SECONDBLOG_TAG, 'label' => __('Magefan SecondBlog Tag Pages')],
            ['value' => Config::PAGE_TITLE_SECONDBLOG_AUTHOR, 'label' => __('Magefan SecondBlog Author Pages')],
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
