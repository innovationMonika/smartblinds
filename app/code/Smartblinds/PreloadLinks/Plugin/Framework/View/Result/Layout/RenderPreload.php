<?php

namespace Smartblinds\PreloadLinks\Plugin\Framework\View\Result\Layout;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\Layout;
use Smartblinds\PreloadLinks\Model\Config;
use Smartblinds\PreloadLinks\Model\Resources;
use Magento\Framework\App\Response\Http as ResponseHttp;

class RenderPreload
{
    private Resources $resources;
    private Config $config;

    public function __construct(
        Resources $resources,
        Config $config
    ) {
        $this->resources = $resources;
        $this->config = $config;
    }

    public function afterRenderResult(
        Layout $subject,
        Layout $result,
        ResponseInterface $httpResponse
    ) {
        if (!$this->config->isRenderEnabled()) {
            return $result;
        }

        $preload = '';
        foreach ($this->resources->getData() as $linkData) {
            $link = '<link ';
            foreach ($linkData as $attribute => $value) {
                $valueWithQuotes = '"' . $value . '"';
                $link .= "$attribute=$valueWithQuotes ";
            }
            $link .= '/>';
            $link .= PHP_EOL;
            $preload .= $link;
        }

        if ($preload) {
            $content = (string)$httpResponse->getContent();
            $title = '<title';
            $position = strpos($content, $title);
            $content = substr_replace($content, $preload . $title, $position, strlen($title));
            $httpResponse->setContent($content);
        }

        return $result;
    }
}
