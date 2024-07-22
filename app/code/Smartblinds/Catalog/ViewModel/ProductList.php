<?php declare(strict_types=1);

namespace Smartblinds\Catalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Smartblinds\Catalog\Model\Config;

class ProductList implements ArgumentInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getAddToCartText($category)
    {
        if (in_array($category->getUrlKey(), $this->config->getAddToCartCategoryUrlKeys())) {
            return __('Add to Cart');
        }
        return __('Assemble');
    }
}
