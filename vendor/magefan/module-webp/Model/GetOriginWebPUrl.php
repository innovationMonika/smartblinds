<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\WebP\Model;

use Magefan\WebP\Api\GetOriginWebPUrlInterface;

/**
 * Retrive WebP image URL using original png,jpg,gif URL
 */
class GetOriginWebPUrl implements GetOriginWebPUrlInterface
{
    /**
     * @var array
     */
    private $webpUrls = [];

    /**
     * Retrive WebP image URL
     * @param string $webPUrl
     * @return string
     */
    public function execute(string $webPUrl): string
    {
        if (strpos($webPUrl, 'mf_webp') === false) {
            return '';
        }

        $array = explode('/', $webPUrl);
        $imageFormat = '';

        for ($i = 0; $i < count($array); $i++) {
            if ($array[$i] == "mf_webp") {
                $imageFormat = $array[$i + 1];
                array_splice($array, $i, 3);
                break;
            }
        }
        $imageUrl = implode('/', $array);

        return str_replace('.webp', '.' . $imageFormat, $imageUrl);
    }
}
