<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace  Magefan\WebP\Api;

/**
 * Convert png,jpg,gif image to WebP using original image URL
 *
 * @api
 * @since 2.1.0
 */
interface CreateWebPImageInterface
{
    /**
     * Convert image to WebP using its URL. Return true if converted successfully or WebP image already exists.
     *
     * @param string $imageUrl
     * @param int|null $mode
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.1.0
     */
    public function execute(string $imageUrl, ?int $mode = null): bool;
}
