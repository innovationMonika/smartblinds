<?php

namespace Smartblinds\Ec\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Layout;

class JsFooterPlugin
{
    private const XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function afterRenderResult(Layout $subject, Layout $result, ResponseInterface $httpResponse)
    {
        if (!$this->isDeferEnabled()) {
            return $result;
        }

        $content = (string)$httpResponse->getContent();
        $bodyEndTag = '</body';
        $bodyEndTagFound = strrpos($content, $bodyEndTag) !== false;

        if ($bodyEndTagFound) {
            $scripts = $this->extractScriptTags($content);
            if ($scripts) {
                $newBodyEndTagPosition = strrpos($content, $bodyEndTag);
                $content = substr_replace($content, $scripts . "\n", $newBodyEndTagPosition, 0);
                $httpResponse->setContent($content);
            }
        }

        return $result;
    }

    private function extractScriptTags(&$content): string
    {
        $scripts = '';
        $scriptOpen = '<script';
        $scriptClose = '</script>';
        $scriptOpenPos = strpos($content, $scriptOpen);

        $skip = [
            'data-skip',
            'vwoCode',
            'var BASE_URL',
            'window.NREUM',
            'text/x-magento-template',
            'text/x-magento-init'
        ];

        while ($scriptOpenPos !== false) {
            $scriptClosePos = strpos($content, $scriptClose, $scriptOpenPos);
            $script = substr($content, $scriptOpenPos, $scriptClosePos - $scriptOpenPos + strlen($scriptClose));
            $isSkip = false;
            foreach ($skip as $skipable) {
                $isSkip = strpos($script, $skipable) !== false;
                if ($isSkip) {
                    break;
                }
            }

            if ($isSkip) {
                $scriptOpenPos = strpos($content, $scriptOpen, $scriptClosePos);
                continue;
            }

            $scripts .= "\n" . $script;
            $content = str_replace($script, '', $content);
            // Script cut out, continue search from its position.
            $scriptOpenPos = strpos($content, $scriptOpen, $scriptOpenPos);
        }

        return $scripts;
    }

    private function isDeferEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
            ScopeInterface::SCOPE_STORE
        );
    }
}
