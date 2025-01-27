<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Plugin\Catalog\Model\Product\Url;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Url;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class ChangeSimpleProductUrl
{
    private Configurable $configurableType;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        Configurable $configurableType,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configurableType = $configurableType;
        $this->productRepository = $productRepository;
    }

    public function afterGetUrl(
        Url $subject,
        $result,
        Product $product,
        $params = []
    ) {
        if ($product->getTypeId() !== Type::TYPE_SIMPLE) {
            return $result;
        }

        $parentProduct = $this->getParentProduct((int)$product->getId());
        if ($parentProduct) {
            return $subject->getUrl($parentProduct, $params) . '#' . http_build_query(['sku' => $product->getSku()]);
        }

        return $result;
    }

    private function getParentProduct(int $productId): ?ProductInterface
    {
        $parentIds = $this->configurableType->getParentIdsByChild($productId);
        $parentId = reset($parentIds);
        if ($parentId) {
            try {
                return $this->productRepository->getById($parentId);
            } catch (Exception $e) {}
        }
        return null;
    }
}
