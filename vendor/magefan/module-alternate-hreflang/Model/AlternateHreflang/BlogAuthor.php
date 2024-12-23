<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\AlternateHreflang;

use Magefan\AlternateHreflang\Model\Config;

class BlogAuthor extends AbstractBlog
{
    /**
     * @return string
     */
    protected function getObjectType()
    {
        return Config::PAGE_TITLE_BLOG_AUTHOR;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->blogFactory->createAuthor();
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->blogFactory->getUrl()::CONTROLLER_AUTHOR;
    }
}
