<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Plugin\Catalog\Helper\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class DontShowConfigurableSimples
{
    private Configurable $configurableType;

    public function __construct(Configurable $configurableType)
    {
        $this->configurableType = $configurableType;
    }

    public function afterCanShow(Product $subject, bool $result, ProductInterface $product)
    {
        if (!$result || $product->getTypeId() !== Type::TYPE_SIMPLE) {
            return $result;
        }

        $hasParents = (bool) $this->configurableType->getParentIdsByChild($product->getId());
        return !$hasParents;
    }
}
