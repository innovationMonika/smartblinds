<?php declare(strict_types=1);

namespace GoMage\Samples\ViewModel;

use GoMage\Samples\Model\Config;
use GoMage\Samples\ViewModel\Product\Loader;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableType;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Smartblinds\System\Model\Product\Attribute\Source\SystemCategory;

class Product implements ArgumentInterface
{
    private Config $config;
    private Image $imageHelper;
    private Json $json;
    private Escaper $escaper;
    private Data $swatchHelper;
    private Media $swatchMediaHelper;
    private AttributeRepositoryInterface $attributeRepository;
    private ConfigurableType $configurableType;
    private Loader $loader;

    public function __construct(
        Config $config,
        Image $imageHelper,
        Json $json,
        Escaper $escaper,
        Data $swatchHelper,
        Media $swatchMediaHelper,
        AttributeRepositoryInterface $attributeRepository,
        ConfigurableType $configurableType,
        Loader $loader
    ) {
        $this->config = $config;
        $this->imageHelper = $imageHelper;
        $this->json = $json;
        $this->escaper = $escaper;
        $this->swatchHelper = $swatchHelper;
        $this->swatchMediaHelper = $swatchMediaHelper;
        $this->attributeRepository = $attributeRepository;
        $this->configurableType = $configurableType;
        $this->loader = $loader;
    }

    public function getProductSampleItemsJson(ProductModel $product): string
    {
        $products = [$product];
        $parentId = null;
        $config = [];
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $parentId = (int) $product->getId();
            $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $products = $this->loader->loadProducts($childrenIds);
            $productData = $this->getProductData($product, $parentId);
            if (!$productData) {
                return $this->json->serialize($config);
            }
            $config[] = $this->getProductData($product);
        }

        if ($product->getTypeId() === Type::TYPE_SIMPLE) {
            $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
            $parentId = reset($parentIds);
            if (!$parentId) {
                return $this->json->serialize($config);
            }
            $parentId = (int) $parentId;
            $parentProducts = $this->loader->loadProducts([$parentId]);
            $parentProduct = $parentProducts->getFirstItem();
            if ($parentProduct) {
                $config[] = $this->getProductData($parentProduct);
            }
        }

        foreach ($products as $product) {
            $productData = $this->getProductData($product, $parentId);
            if (!$productData) {
                continue;
            }
            $config[] = $productData;
        }
        return $this->json->serialize($config);
    }

    /**
     * @param $product
     * @return bool
     */
    public function haveSamples($product): bool
    {
        try {
            $samples = $this->json->unserialize($this->getProductSampleItemsJson($product));
        } catch (\Exception) {
            $samples = [];
        }
        return !empty($samples);
    }

    public function getAddToCartIcon(): string
    {
        return '+';
    }

    public function getAddedToCartIcon(): string
    {
        return '✓';
    }

    public function getAddToCartClass(): string
    {
        return 'notadd';
    }

    public function getAddedToCartClass(): string
    {
        return 'added';
    }

    private function getProductData(ProductModel $product, ?int $parentId = null): ?array
    {
        $swatches = [];
        $swatchesCodes = ['color'];
        $systemCategory = $product->getSystemCategory();
        if ($systemCategory === SystemCategory::ROLLER) {
            $swatchesCodes[] = 'transparency';
        }
        foreach ($swatchesCodes as $attributeCode) {
            $swatches[$attributeCode] = $this->getSwatchData($product, $attributeCode);
        }
        $swatches = array_filter($swatches);

        if ($product->getTypeId() !== Configurable::TYPE_CODE && sizeof($swatches) !== sizeof($swatchesCodes)) {
            return null;
        }

        $data = [
            'parentId' => $parentId,
            'type' => $product->getTypeId(),
            'id' => (int)$product->getId(),
            'name' => $this->escaper->escapeHtml($product->getName()),
            'url' => $this->escaper->escapeUrl($product->getProductUrl()),
            'image' => $this->escaper->escapeUrl(
                $this->imageHelper->init($product, $this->config->getProductImageAttribute())->getUrl()
            ),
            'swatches' => $swatches,
            'systemCategory' => $systemCategory
        ];

        if ($systemCategory === SystemCategory::HONEYCOMB) {
            $data['transparency'] = $this->getAttributeOptionLabel($product, 'transparency');
        }

        return $data;
    }

    private function getAttributeOptionLabel(ProductModel $product, string $attributeCode) {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        $optionId = $product->getData($attributeCode);
        if (!$optionId) {
            return null;
        }
        return $this->getDefaultAttributeOptionLabel($attribute, $optionId);
    }

    private function getSwatchData(ProductModel $product, string $attributeCode): ?array
    {
        try {
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        if (!$this->swatchHelper->isSwatchAttribute($attribute)) {
            return null;
        }

        $optionId = $product->getData($attributeCode);
        if (!$optionId) {
            return null;
        }

        $optionLabel = $this->getDefaultAttributeOptionLabel($attribute, $optionId);

        return [
            'optionId' => (int)$optionId,
            'optionLabel' => $this->escaper->escapeHtml($optionLabel),
            'imageUrl' => $this->escaper->escapeUrl($this->getSwatchImageUrl($product, $optionId))
        ];
    }

    private function getDefaultAttributeOptionLabel($attribute, $optionId)
    {
        $previousStoreId = $attribute->getStoreId();
        $attribute->setStoreId(0);
        $optionLabel = $attribute->getSource()->getOptionText($optionId);
        $attribute->setStoreId($previousStoreId);
        return $optionLabel;
    }

    private function getSwatchImageUrl(ProductModel $product, $optionId): ?string
    {
        if ($product->getData(Swatch::SWATCH_IMAGE_NAME)) {
            return $this->imageHelper->init($product, Swatch::SWATCH_IMAGE_NAME)->getUrl();
        }
        $swatchesData = $this->swatchHelper->getSwatchesByOptionsId([$optionId]);
        $swatchData   = reset($swatchesData);
        if (!$swatchData) {
            return null;
        }
        return $this->swatchMediaHelper->getSwatchAttributeImage(
            Swatch::SWATCH_IMAGE_NAME,
            $swatchData['value']
        );
    }
}
