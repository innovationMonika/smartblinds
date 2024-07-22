<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Model\Product\Type;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class Configurable extends \Magento\ConfigurableProduct\Model\Product\Type\Configurable
{
    private StoreManagerInterface $storeManager;

    public function getSelectedAttributesInfo($product)
    {
        $attributes = [];
        \Magento\Framework\Profiler::start(
            'CONFIGURABLE:' . __METHOD__,
            ['group' => 'CONFIGURABLE', 'method' => __METHOD__]
        );
        if ($attributesOption = $product->getCustomOption('attributes')) {
            $data = $attributesOption->getValue();
            if (!$data) {
                return $attributes;
            }
            $data = $this->serializer->unserialize($data);
            $this->getUsedProductAttributeIds($product);

            $usedAttributes = $product->getData($this->_usedAttributes);

            foreach ($data as $attributeId => $attributeValue) {
                if (isset($usedAttributes[$attributeId])) {
                    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                    $attribute = $usedAttributes[$attributeId]->getProductAttribute();
                    $attribute->setStoreId($this->getStoreManager()->getStore()->getId());
                    $label = $attribute->getStoreLabel();
                    $value = $attribute;
                    if ($value->getSourceModel()) {
                        $value = $value->getSource()->getOptionText($attributeValue);
                    } else {
                        $value = '';
                        $attributeValue = '';
                    }

                    $attributes[] = [
                        'label' => $label,
                        'value' => $value,
                        'option_id' => $attributeId,
                        'option_value' => $attributeValue
                    ];
                }
            }
        }
        \Magento\Framework\Profiler::stop('CONFIGURABLE:' . __METHOD__);
        return $attributes;
    }

    private function getStoreManager(): StoreManagerInterface
    {
        if (!isset($this->storeManager)) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }
        return $this->storeManager;
    }
}
