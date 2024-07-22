<?php declare(strict_types=1);

namespace Smartblinds\Options\Block\Catalog\Product\View\Options\Type;

use JMS\Serializer\Handler\StdClassHandler;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Smartblinds\Options\Model\Config;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory;

class WidthHeight extends AbstractOptions
{
    private Config $config;
    private CollectionFactory $collectionFactory;


    public function __construct(
        Context $context,
        PricingHelper $pricingHelper,
        Data $catalogData,
        Config $config,
        CollectionFactory $collectionFactory,
        array $data = [],
        CalculateCustomOptionCatalogRule $calculateCustomOptionCatalogRule = null,
        CalculatorInterface $calculator = null,
        PriceCurrencyInterface $priceCurrency = null
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $pricingHelper, $catalogData, $data,
            $calculateCustomOptionCatalogRule, $calculator, $priceCurrency);
    }

    public function getMeasurementMessage(): string
    {
        return $this->config->getMeasurementMessage();
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function getDefaultValue($field = '')
    {
        $defaultValues = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $this->getOption()->getId());
        if ($defaultValues) {
            $values = json_decode($defaultValues);
            $valuesArray = get_object_vars($values);

            return $field ? floatval($valuesArray[$field])/10 : json_encode($values);
        }
        return '';
    }
}
