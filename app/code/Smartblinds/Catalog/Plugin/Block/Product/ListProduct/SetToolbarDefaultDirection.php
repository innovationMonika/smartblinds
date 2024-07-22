<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Plugin\Block\Product\ListProduct;

class SetToolbarDefaultDirection
{
    public function afterGetToolbarBlock(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $result
    ) {
        if ($result instanceof \Magento\Framework\View\Element\Template) {
            $result->setData('direction', 'asc');
        }
        return $result;
    }
}
