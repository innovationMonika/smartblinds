<?php

namespace Smartblinds\ConfigurableSimple\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Swatches\Helper\Media;

class ColorsInfo implements ArgumentInterface
{
    private Media $media;
    private Json $json;

    public function __construct(
        Media $media,
        Json $json
    ) {
        $this->media = $media;
        $this->json = $json;
    }

    public function getColorsUrls(Product $product)
    {
        try {
            $colors = $this->getUnserializedColorsInfo($product)['colors'] ?? [];
            return array_map(function ($color) {
                return $this->media->getSwatchAttributeImage(
                    'swatch_thumb',
                    $color
                );
            }, $colors);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getColorsLeftCount(Product $product)
    {
        try {
            return $this->getUnserializedColorsInfo($product)['leftCount'] ?? [];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getUnserializedColorsInfo(Product $product)
    {
        return $this->json->unserialize($product->getData('colors_info'));
    }
}
