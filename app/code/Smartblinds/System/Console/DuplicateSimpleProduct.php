<?php

namespace Smartblinds\System\Console;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\SaveHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;

class DuplicateSimpleProduct extends Command
{
    private Product $productResourceModel;
    private CollectionFactory $productCollection;
    private ProductRepository $productRepository;
    private Repository $attributeRepository;
    private State $state;
    private Copier $copier;
    private LoggerInterface $logger;
    private Configurable $configurableType;
    private SaveHandler $saveHandler;
    private \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable;
    private ConfigurableAttribute $configurableAttribute;

    public function __construct(
        Product $productResourceModel,
        CollectionFactory $productCollection,
        ProductRepository $productRepository,
        Repository $attributeRepository,
        State $state,
        Copier $copier,
        LoggerInterface $logger,
        Configurable $configurableType,
        SaveHandler $saveHandler,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        ConfigurableAttribute $configurableAttribute,
        string $name = null
    ) {
        parent::__construct($name);
        $this->productResourceModel = $productResourceModel;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->state = $state;
        $this->copier = $copier;
        $this->logger = $logger;
        $this->configurableType = $configurableType;
        $this->saveHandler = $saveHandler;
        $this->configurable = $configurable;
        $this->configurableAttribute = $configurableAttribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('simple:control_type:duplicate');
        $this->setDescription('Duplicate simple products for control type Chain');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set area code
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);

            $output->writeln('<info>Starting duplicate</info>');
            $configProducts = $this->getConfigProducts();
            if (!empty($configProducts)) {
                //$this->clearConfigProducts($configProducts);

                $this->setProductControlType();
                $this->addProductsConfigProducts($configProducts);
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</error>');
        }
        $output->writeln('<info>Complete</info>');
    }

    protected function getConfigProducts($attributeCode = "control_type")
    {
        $products = [];
        $attributeObject = $this->attributeRepository->get($attributeCode);
        $systemType = $this->attributeRepository->get('system_type');
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect("*");
        $collection->addFieldToFilter("type_id", "configurable");
        /**
         * @var \Magento\Catalog\Model\Product $item
         */
        foreach ($collection as $item) {
            $ids = $this->configurableType->getUsedProductAttributeIds($item);
            if (!in_array($attributeObject->getAttributeId(), $ids) && in_array($systemType->getAttributeId(), $ids)) {
                $products[] = $item;
            }
        }

        return $products;
    }

    protected function clearConfigProducts($configProducts)
    {
        foreach ($configProducts as $configProduct) {
            $this->configurable->saveProducts($configProduct, []);
        }
    }

    protected function addProductsConfigProducts($configProducts, $attributeCode = "control_type")
    {
        $attributeObject = $this->attributeRepository->get($attributeCode);
        //print_r($attributeObject->getAttributeId()); exit(1);
        /**
         * @var \Magento\Catalog\Model\Product $configProduct
         */
        foreach ($configProducts as $configProduct) {
            $attributeIds = $this->configurableType->getUsedProductAttributeIds($configProduct);
            $attributeIds[] = $attributeObject->getAttributeId();
            $simplesIds = $configProduct->getTypeInstance()->getChildrenIds($configProduct->getId());

            $simpleIds = [];
            foreach ($simplesIds[0] as $simpleId) {
                $childProduct = $this->productRepository->getById($simpleId);
                $simpleIds[] = $childProduct->getId();

                /**
                 * @var Collection $collection
                 */
                $collection = $this->productCollection->create();
                $collection->addFieldToFilter('sku', $childProduct->getSku() . '-C')
                    ->setPage(1, 1);
                if ($collection->count() > 0) {
                    $simpleIds[] = $collection->getFirstItem()->getId();
                }
            }

            $simpleIds = array_unique($simpleIds);

            $usedProductAttributeIds = $attributeIds;

            // Create option if not created
            $createOptionAttributes = $usedProductAttributeIds;

            $position = 3;

            $configurableProduct = $this->productRepository->get($configProduct->getSku(), false, null, true);
            foreach ($configurableProduct->getExtensionAttributes()->getConfigurableProductOptions() as $optionAttribute) {
                if (($key = array_search($optionAttribute->getAttributeId(), $createOptionAttributes,
                        true)) !== false) {
                    if((int)$optionAttribute->getPosition() >= $position) {
                        $optionAttribute->setPosition((int)$optionAttribute->getPosition()+1)->save();
                    }
                    unset($createOptionAttributes[$key]);
                }
            }

            foreach ($createOptionAttributes as $attributeId) {
                $options = [];
                foreach ($attributeObject->getOptions() as $option) {
                    $options[] =['value' => $option->getValue(), 'label' => $option->getLabel()];
                }
                $data = [
                    'attribute_id' => $attributeId,
                    'product_id' => $configurableProduct->getId(),
                    'position' => $position,
                    'options' => $options,
                ];
                $this->configurableAttribute->setData($data)->save();
                $position++;
            }
            //$this->configurable->saveProducts($configurableProduct, []);

            $this->configurable->saveProducts($configProduct, array_unique($simpleIds));
        }
    }

    protected function setProductControlType(
        $attributeCode = "control_type"
    ) {
        $attributeObject = $this->attributeRepository->get($attributeCode);
        $options = $attributeObject->getOptions();
        $motorId = null;
        $chainId = null;
        foreach ($options as $option) {
            if ($option->getLabel() === 'Motor') {
                $motorId = $option->getValue();
            } elseif ($option->getLabel() === 'Chain') {
                $chainId = $option->getValue();
            }
        }
        /**
         * @var Collection $collection
         */
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect("*");
        $collection->addFieldToFilter("type_id", "simple");
        //$collection->addFieldToFilter("status", 1);
        $collection->addFieldToFilter("system_type", ['notnull' => true]);
        $collection->addFieldToFilter("control_type", ['null' => true]);
        /*$collection->setPage(1, 50);*/
        foreach ($collection as $item) {
            $updatedProduct = $this->productRepository->getById($item->getId());
            $updatedProduct->setData('control_type', $motorId);
            try {
                $updatedProductForClone = $this->productRepository->getById($item->getId());
                $updatedProductForClone->setData('sku', $updatedProductForClone->getSku() . '-C');
                $updatedProductForClone->setData('url_key', $updatedProductForClone->getUrlKey() . '-chain');
                $clonedProduct = $this->copier->copy($updatedProductForClone);
                $clonedProduct->setData('control_type', $chainId);
                $clonedProduct->setData('status', $item->getStatus());
                $this->productResourceModel->saveAttribute($updatedProduct, 'control_type');
                $this->productResourceModel->saveAttribute($clonedProduct, 'control_type');
                $this->productResourceModel->saveAttribute($clonedProduct, 'status');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
