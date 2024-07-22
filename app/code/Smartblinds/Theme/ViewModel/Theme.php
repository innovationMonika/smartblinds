<?php declare(strict_types=1);

namespace Smartblinds\Theme\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Smartblinds\Theme\Model\Config;

class Theme implements ArgumentInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getHeadContent(): string
    {
        return $this->config->getHeadContent();
    }
}
