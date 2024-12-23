<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace  Magefan\WebP\Api;

/**
 * Parse AMP HTML and add WebP images
 *
 * @api
 * @since 2.1.0
 */
interface AmpHtmlParserInterface
{
    /**
     * @param string $output
     * @return string
     * @since 2.1.0
     */
    public function execute(string $output): string;

    /**
     * Return New HTML Tag with original and webp images
     * @param string $imageUrl
     * @param string $htmlTag
     * @return string
     * @since 2.1.0
     */
    public function getNewHtmlTag(string $imageUrl, string $htmlTag): string;
}
