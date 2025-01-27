<?php

namespace Smartblinds\WebP\Plugin\Swatches\Block\Product\Renderer;

use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Model\Config;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Swatches\Block\Product\Renderer\Configurable as ConfigurableRenderer;
use Magento\Swatches\Model\Swatch;

class Configurable
{
    private CreateWebPImageInterface $createWebPImage;
    private GetWebPUrlInterface $getWebPUrl;
    private Config $config;
    private Json $json;

    public function __construct(
        CreateWebPImageInterface $createWebPImage,
        GetWebPUrlInterface $getWebPUrl,
        Config $config,
        Json $json
    ) {
        $this->createWebPImage = $createWebPImage;
        $this->getWebPUrl = $getWebPUrl;
        $this->config = $config;
        $this->json = $json;
    }

    public function afterGetJsonSwatchConfig(
        ConfigurableRenderer $subject,
        $result
    ) {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        $result = $this->json->unserialize($result);

        foreach ($result as $attributeId => $options) {
            foreach ($options as $optionId => $optionData) {
                if (!is_array($optionData)) {
                    continue;
                }
                if ($optionData['type'] !== Swatch::SWATCH_TYPE_VISUAL_IMAGE) {
                    continue;
                }
                $result[$attributeId][$optionId]['value'] = $this->convertToWebp($optionData['value']);
                $result[$attributeId][$optionId]['thumb'] = $this->convertToWebp($optionData['thumb']);
            }
        }

        return $this->json->serialize($result);
    }

    private function convertToWebp(string $image): string
    {
        return $this->createWebPImage->execute($image) ? $this->getWebPUrl->execute($image) : $image;
    }
}
