<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Api;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Retrieve Alternate Hreflang URLs for object
 * by it's type, e.g. homepage, product, category, cms, blog_post, blog_category, blog_index, blog_tag, blog_author
 * secondblog_post, secondblog_category, secondblog_index, secondblog_tag, secondblog_author
 *
 * @api
 * @since 2.1.0
 */
interface GetAlternateHreflangInterface
{
    /**
     * @param mixed $object
     * @param string $type
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function execute($object, $type);
}
