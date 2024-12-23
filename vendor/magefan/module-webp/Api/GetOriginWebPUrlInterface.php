<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace  Magefan\WebP\Api;

/**
 * Retrive jpg,png,gif image URL using WebP URL
 *
 * @api
 * @since 2.1.0
 */
interface GetOriginWebPUrlInterface
{
    /**
     * Retrive Origin image URL.
     * Return empty string if origin URL cannot be created
     *
     * @param string $webPUrl
     * @return string
     * @since 2.1.0
     */
    public function execute(string $webPUrl): string;
}
