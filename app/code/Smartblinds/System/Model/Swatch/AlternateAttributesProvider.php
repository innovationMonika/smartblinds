<?php declare(strict_types=1);

namespace Smartblinds\System\Model\Swatch;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Swatches\Model\SwatchAttributeCodes;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Swatches\Model\SwatchAttributeType;

class AlternateAttributesProvider extends SwatchAttributesProvider
{
    private Configurable $typeConfigurable;
    private SwatchAttributeCodes $swatchAttributeCodes;
    private AttributeRepositoryInterface $attributeRepository;
    private SwatchAttributeType $swatchTypeChecker;

    private $attributesPerProduct;

    public function __construct(
        Configurable $typeConfigurable,
        SwatchAttributeCodes $swatchAttributeCodes,
        AttributeRepositoryInterface $attributeRepository,
        SwatchAttributeType $swatchTypeChecker
    ) {
        $this->typeConfigurable = $typeConfigurable;
        $this->swatchAttributeCodes = $swatchAttributeCodes;
        $this->attributeRepository = $attributeRepository;
        $this->swatchTypeChecker = $swatchTypeChecker;
    }

    public function provide(Product $product)
    {
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return [];
        }
        if (!isset($this->attributesPerProduct[$product->getId()])) {
            $configurableAttributes = $this->typeConfigurable->getConfigurableAttributes($product);
            $swatchAttributeCodeMap = $this->swatchAttributeCodes->getCodes();

            $swatchAttributes = [];
            foreach ($configurableAttributes as $configurableAttribute) {
                if (array_key_exists($configurableAttribute->getAttributeId(), $swatchAttributeCodeMap)) {
                    /** @var AbstractAttribute $productAttribute */
                    $productAttribute = $configurableAttribute->getProductAttribute();
                    if ($productAttribute !== null
                        && $this->swatchTypeChecker->isSwatchAttribute($productAttribute)
                        && in_array($productAttribute->getAttributeCode(), ['system_type', 'control_type', 'system_size', 'system_color'])
                    ) {
                        $alternateAttribute = $this->attributeRepository->get(
                            Product::ENTITY,
                            $productAttribute->getAttributeCode() . '_alternate'
                        );
                        $swatchAttributes[$alternateAttribute->getAttributeId()] = $alternateAttribute;
                    }
                }
            }
            $this->attributesPerProduct[$product->getId()] = $swatchAttributes;
        }
        return $this->attributesPerProduct[$product->getId()];
    }
}
