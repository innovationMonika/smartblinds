<?php

namespace Smartblinds\PreloadLinks\Plugin\Catalog\Block\Product\ListProduct;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Smartblinds\PreloadLinks\Model\Config;
use Smartblinds\PreloadLinks\Model\Resources;

class AddLinks
{
    private Resources $resources;
    private Config $config;
    private bool $imageAdded = false;

    public function __construct(
        Resources $resources,
        Config $config
    ) {
        $this->resources = $resources;
        $this->config = $config;
    }

    public function afterGetImage(
        ListProduct $subject,
        Image $result,
        Product $product,
        $location,
        array $attributes = []
    ) {
        if (!$this->config->isCollectForCategoryEnabled() || $this->imageAdded) {
            return $result;
        }
        if (in_array($location, $this->config->getCategoryImageDisplayAreas())) {
            $this->resources->add($result->getImageUrl(), 'image');
            $this->imageAdded = true;
        }
        return $result;
    }
}
