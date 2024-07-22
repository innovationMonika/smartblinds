<?php declare(strict_types=1);

namespace Smartblinds\System\Plugin\ConfigurableProduct\Block\Product\View\Type\Configurable;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json;
use Smartblinds\System\Model\Config as SystemConfig;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class AddSystems
{
    private Repository $repositoryAttribute;
    private Json $json;
    private CollectionFactory $collectionFactory;
    private SystemConfig $systemConfig;

    public function __construct(
        Json $json,
        CollectionFactory $collectionFactory,
        Repository $repositoryAttribute,
        PriceCurrencyInterface $priceCurrency,
        SystemConfig $systemConfig
    ) {
        $this->json = $json;
        $this->collectionFactory = $collectionFactory;
        $this->repositoryAttribute = $repositoryAttribute;
        $this->priceCurrency = $priceCurrency;
        $this->systemConfig = $systemConfig;
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        string $result
    ) {
        $systemCategory = $subject->getProduct()->getSystemCategory();

        $config = $this->json->unserialize($result);
        $systems = $this->collectionFactory->create()
            ->addFieldToFilter('system_category', ['eq' => $systemCategory])
            ->getItems();

        foreach ($systems as $system) {
            /** @var @system \Smartblinds\System\Model\System */
            $config['systems'][] = [
                'systemType' => $system->getData('system_type'),
                'controlType' => $system->getData('control_type'),
                'controlTypeData' => $this->getControlType(),
                'systemSize' => $system->getData('system_size'),
                'fabricSize' => $system->getData('fabric_size'),
                'systemCategory' => $system->getData('system_category'),
                'priceCoefficient' => $system->getData('price_coefficient'),
                'systemDiameter' => (float) $system->getData('system_diameter'),
                'bottomBarWeight' => (float) $system->getData('bottom_bar_weight'),
                'tubeDiameter' => (float) $system->getData('tube_diameter'),
                'tubeWeight' => (float) $system->getData('tube_weight'),
                'tube384Ei' => (float) $system->getData('tube_384_ei'),
                'torque' => (float) $system->getData('torque'),
                'bending' => (float) $system->getData('bending'),
                'operatingForce' => (float) $system->getData('operating_force'),
                'operatingRatio' => (float) $system->getData('operating_ratio'),
                'basePrice' => $this->priceCurrency->convert((float) $system->getStoreBasePrice()),
                'meterPrice' => $this->priceCurrency->convert((float) $system->getStoreMeterPrice()),
                'minWidth' => (float) $system->getData('min_width'),
                'minHeight' => (float) $system->getData('min_height'),
                'maxWidth' => (float) $system->getStoreMaxWidthPrice(),
                'maxHeight' => (float) $system->getStoreMaxHeightPrice(),
                'isChainCustomerGroup' => $this->systemConfig->isShowControlType(),
            ];
        }
        $config['systemSizeValues'] = $this->getSystemSizesJson();
        $config['fabricSizeValues'] = $this->getFabricSizesJson();
        $config['systemTypeValues'] = $this->getSystemTypesJson();
        return $this->json->serialize($config);
    }

    private function getSystemSizesJson()
    {
        $sizeAttribute = $this->repositoryAttribute->get('system_size');
        $sizeAttribute->setStoreId(0);
        $systemSizes = [];
        foreach ($sizeAttribute->getOptions() as $option) {
            if ($option->getValue()) {
                $systemSizes[$option->getValue()] = strtolower($option->getLabel());
            }
        }
        return $systemSizes;
    }

    private function getFabricSizesJson()
    {
        $sizeAttribute = $this->repositoryAttribute->get('fabric_size');
        $sizeAttribute->setStoreId(0);
        $systemSizes = [];
        foreach ($sizeAttribute->getOptions() as $option) {
            if ($option->getValue()) {
                $label = str_replace(" ", "", strtolower($option->getLabel()));
                $systemSizes[$option->getValue()] = $label;
                $systemSizes[$label] = $option->getValue();
            }
        }
        return $systemSizes;
    }

    private function getSystemTypesJson()
    {
        $sizeAttribute = $this->repositoryAttribute->get('system_type');
        $sizeAttribute->setStoreId(0);
        $systemSizes = [];
        foreach ($sizeAttribute->getOptions() as $option) {
            if ($option->getValue()) {
                $label = str_replace(" ", "", strtolower($option->getLabel()));
                $systemSizes[$option->getValue()] = $label;
                $systemSizes[$label] = $option->getValue();
            }
        }
        return $systemSizes;
    }

    private function getControlType()
    {
        $controlAttribute = $this->repositoryAttribute->get('control_type');
        $controlAttribute->setStoreId(0);
        $controlTypeOptions = ['attributeId' => $controlAttribute->getAttributeId(), 'options' => []];
        foreach ($controlAttribute->getOptions() as $option) {
            $controlTypeOptions['options'][$option->getValue()] = strtolower($option->getLabel());
            $controlTypeOptions['options'][strtolower($option->getLabel())] = $option->getValue();
        }
        return $controlTypeOptions;
    }
}
