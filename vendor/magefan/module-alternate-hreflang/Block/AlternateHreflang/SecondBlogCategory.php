<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Block\AlternateHreflang;

use Magefan\AlternateHreflang\Block\AlternateHreflang;
use Magefan\AlternateHreflang\Model\Config;

/**
 * Class Blog Category Alternate Hreflang
 */
class SecondBlogCategory extends AlternateHreflang
{
    /**
     * @return object
     */
    public function getCurrentObject()
    {
        return $this->coreRegistry->registry('current_secondblog_category');
    }

    /**
     * @return string
     */
    protected function getObjectType()
    {
        return Config::PAGE_TITLE_SECONDBLOG_CATEGORY;
    }
}
