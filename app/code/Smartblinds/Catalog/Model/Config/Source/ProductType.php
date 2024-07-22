<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Model\Config\Source;

use Magento\Catalog\Api\Data\ProductTypeInterface;
use Magento\Catalog\Model\ProductTypeList;
use Magento\Framework\Data\OptionSourceInterface;

class ProductType implements OptionSourceInterface
{
    private ProductTypeList $productTypeList;

    public function __construct(ProductTypeList $productTypeList)
    {
        $this->productTypeList = $productTypeList;
    }

    public function toOptionArray()
    {
        return array_map(fn (ProductTypeInterface $type) => [
            'label' => $type->getLabel(),
            'value' => $type->getName()
        ], $this->productTypeList->getProductTypes());
    }
}
