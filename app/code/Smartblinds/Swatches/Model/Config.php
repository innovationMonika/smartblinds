<?php declare(strict_types=1);

namespace Smartblinds\Swatches\Model;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;

class Config
{
    private SwatchCollectionFactory $swatchCollectionFactory;

    public function __construct(SwatchCollectionFactory $swatchCollectionFactory)
    {
        $this->swatchCollectionFactory = $swatchCollectionFactory;
    }

    public function getLargeSwatchesIds()
    {
        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = ObjectManager::getInstance()->get(AttributeRepositoryInterface::class);
        $attributes = ['system_type', 'control_type', 'fabric_size', 'system_size', 'system_size_alternate', 'system_type_alternate',];
        $ids = [];
        foreach ($attributes as $attributeCode) {
            try {
                $attribute = $attributeRepository->get('catalog_product', $attributeCode);

                $options = $attribute->getSource()->getAllOptions(false);
                $optionIds = array_map(function ($optionData) {
                    return $optionData['value'];
                }, $options);

                /** @var \Magento\Swatches\Model\ResourceModel\Swatch\Collection $swatchCollection */
                $swatchCollection = $this->swatchCollectionFactory->create();
                $swatchCollection->addFilterByOptionsIds($optionIds);

                $ids = array_merge($ids, array_map(function ($swatch) {
                    return $swatch->getSwatchId();
                }, $swatchCollection->getItems()));
            } catch (NoSuchEntityException $e) {
            }
        }
        return $ids;
    }
}
