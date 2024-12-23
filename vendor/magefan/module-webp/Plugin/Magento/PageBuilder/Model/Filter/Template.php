<?php

namespace Magefan\WebP\Plugin\Magento\PageBuilder\Model\Filter;

use Magefan\WebP\Model\Config;
use Magefan\WebP\Model\Parser\PageBuilder as Parser;

class Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * Template constructor.
     * @param Config $config
     * @param Parser $parser
     */
    public function __construct(
        Config $config,
        Parser $parser
    ) {
        $this->config = $config;
        $this->parser = $parser;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed|string|string[]
     */
    public function afterFilter($subject, $result)
    {
        return ($this->config->isEnabled() && $this->config->convertImagesInsertedViaPageBuilder()) ?
            $this->parser->processBackgroundImages($result) :  $result  ;
    }
}
