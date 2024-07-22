<?php declare(strict_types=1);

namespace Smartblinds\Theme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Smartblinds\Theme\Model\Config;

class Critical extends AbstractHelper
{
    private RequestInterface $request;
    private Config $moduleConfig;
    private array $config;

    public function __construct(
        Context $context,
        RequestInterface $request,
        Config $moduleConfig,
        $config = []
    ) {
        $this->request = $request;
        $this->moduleConfig = $moduleConfig;
        $this->config = $config;
        parent::__construct($context);
    }

    public function isCriticalPage(): bool
    {
        return $this->moduleConfig->isCssCriticalEnabled() && isset($this->config[$this->request->getFullActionName()]);
    }

    public function getCriticalFile(): string
    {
        return $this->config[$this->request->getFullActionName()] ?? 'css/critical.css';
    }
}
