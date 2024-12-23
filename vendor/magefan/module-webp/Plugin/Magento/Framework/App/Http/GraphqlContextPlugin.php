<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Plugin\Magento\Framework\App\Http;

use Magefan\WebP\Model\Config;
use Magento\Framework\App\Http\Context;

/**
 * Class GraphqlContextPlugin
 * @package Magefan\WebP\Plugin\Magento\Framework\App\Http
 */
class GraphqlContextPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * ContextPlugin constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Context $subject
     * @param $result
     * @return string
     */
    public function afterGetVaryString(Context $subject, $result)
    {
        if (!$this->config->isEnabled() || !$this->config->isConvertImagesInGraphQLRequests() || !$this->config->isBrowserWebPCompatible()) {
            return $result;
        }

        return $result . 'webp';
    }
}
