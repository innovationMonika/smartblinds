<?php

namespace Smartblinds\ConfigurableProduct\Block\Cart\Item\Renderer\Actions;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Edit extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit
{
    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        $product = $this->getItem()->getProduct();
        $configureUrl = parent::getConfigureUrl();
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $configureUrl .= '#'. http_build_query(['sku' => $product->getSku()]);
        }
        return $configureUrl;
    }
}
