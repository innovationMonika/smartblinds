<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Plugin\ConfigurableProduct\Block\Product\View\Type\Configurable;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json;

class AddAttributes
{
    private Json $json;
    private CollectionFactory $collectionFactory;

    public function __construct(
        Json $json,
        CollectionFactory $collectionFactory
    ) {
        $this->json = $json;
        $this->collectionFactory = $collectionFactory;
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        string $result
    ) {
        $config = $this->json->unserialize($result);
        $ids = array_keys($config['index']);
        $productsCollection = $this->collectionFactory->create();
        $productsCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
        $additionalAttributes = [
            'weight',
            'thickness',
            'railroad',
            'width',
            'delivery_terms',
            'max_width',
            'max_height',
            'control_type',
        ];
        $productsCollection->addAttributeToSelect($additionalAttributes);
        foreach ($additionalAttributes as $attribute) {
            $defaultValue = (!in_array($attribute, ['delivery_terms'])) ? 0 : '';
            $isMaxAttribute = in_array($attribute, ['max_width', 'max_height']);
            $defaultValue = $isMaxAttribute ? null : $defaultValue;
            $config['additionalAttributes'][$attribute] = [];
            foreach ($productsCollection as $product) {
                $value = $product->getData($attribute) ?? $defaultValue;
                if ($attribute == 'railroad') {
                    $value = (bool) $value;
                }
                if ($isMaxAttribute && $value !== $defaultValue) {
                    $value = (float) $value;
                }
                $config['additionalAttributes'][$product->getId()][$attribute] = $value;
            }
        }
        return $this->json->serialize($config);
    }
}
