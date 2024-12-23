<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\WebP\Api;

/**
 * Retrive WebP image using image
 *
 * @api
 */
interface GetWebPPathInterface
{
    /**
     * Retrive WebP image
     *
     * @param string $image
     * @return string
     */
    public function execute(string $image): string;
}
