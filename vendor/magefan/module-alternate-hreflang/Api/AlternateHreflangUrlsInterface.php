<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Api;

interface AlternateHreflangUrlsInterface
{
    /**
     * @param $currentObject
     * @return mixed
     */
    public function getAlternateUrls($currentObject);
}