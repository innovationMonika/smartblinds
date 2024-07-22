<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Source;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;

class MediaImageAttribute
{
    private Config $eavConfig;

    public function __construct(Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    public function toOptionArray()
    {
        $optionArray = [];

        /** @var Collection $collection */
        $collection = $this->eavConfig->getEntityType(Product::ENTITY)
            ->getAttributeCollection()
            ->addFieldToFilter(AttributeInterface::FRONTEND_INPUT, 'media_image');

        /** @var Attribute $attribute */
        foreach ($collection as $attribute) {
            $optionArray[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel()
            ];
        }

        return $optionArray;
    }
}
