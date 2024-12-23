<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\WebP\Api;

/**
 * Retrive needSkip image status
 *
 * @api
 */
interface ImageNeedSkipInterface
{
    /**
     * Retrive needSkip image status
     *
     * @param string $image
     * @return bool
     */
    public function execute(string $image): bool;
}
