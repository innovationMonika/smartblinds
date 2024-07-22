<?php declare(strict_types=1);

namespace Smartblinds\Theme\Plugin\Controller\Result\AsyncCssPlugin;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Layout;
use Smartblinds\Theme\Helper\Critical;

class PreventNonCritical
{
    private Critical $critical;

    public function __construct(Critical $critical)
    {
        $this->critical = $critical;
    }

    public function aroundAfterRenderResult(
        \Magento\Theme\Controller\Result\AsyncCssPlugin $asyncCss,
        callable $proceed,
        Layout $subject,
        Layout $result,
        ResponseInterface $httpResponse
    ) {
        if ($this->critical->isCriticalPage()) {
            return $proceed($subject, $result, $httpResponse);
        }
        return $result;
    }
}
