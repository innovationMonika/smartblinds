<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace  Magefan\WebP\Api;

/**
 * Retrive WebP image URL using original png,jpg,gif URL
 *
 * @api
 * @since 2.1.0
 */
interface GetWebPUrlInterface
{
    /**
     * Retrive WebP image URL
     *
     * @param string $imageUrl
     * @return string
     * @since 2.1.0
     */
    public function execute(string $imageUrl): string;
}
