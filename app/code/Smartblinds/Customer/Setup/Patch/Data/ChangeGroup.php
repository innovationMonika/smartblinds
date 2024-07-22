<?php declare(strict_types=1);

namespace Smartblinds\Customer\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Eav\Model\Attribute;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;

class ChangeGroup implements DataPatchInterface
{
    protected CollectionFactory $collectionFactory;
    protected ScopeConfigInterface $scopeConfig;
    protected CustomerRepository $customerRepository;
    protected QuoteFactory $quoteFactory;
    protected State $state;
    protected Attribute $attribute;
    protected QuoteRepository $quoteRepository;
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepository $customerRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteRepository $quoteRepository
     * @param State $state
     * @param Attribute $attribute
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        CustomerRepository $customerRepository,
        QuoteFactory $quoteFactory,
        QuoteRepository $quoteRepository,
        State $state,
        Attribute $attribute
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
        $this->state = $state;
        $this->attribute = $attribute;
        $this->quoteRepository = $quoteRepository;
    }

    public function apply()
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            //do nothing
        }
        $attributeControlType = $this->attribute->loadByCode(Product::ENTITY, 'control_type');
        $attributeControlTypeId = $attributeControlType->getId();
        $attributeControlTypeValue = $attributeControlType->getDefaultValue();
        $attributeSystemType = $this->attribute->loadByCode(Product::ENTITY, 'system_type')->getId();
        /**
         * @var Collection $customers
         */
        $customers = $this->collectionFactory->create();
        foreach ($customers as $customer) {
            try {
                $customer = $this->customerRepository->getById($customer->getId());
                $quote = $this->quoteRepository->getForCustomer($customer->getId());
                $items = $quote->getAllItems();
                foreach ($items as $item) {
                    $opt = $item->getOptionByCode('info_buyRequest')->getData();
                    $values = json_decode($opt['value'], true);
                    $doSave = false;
                    if (!empty($values['options'][$attributeSystemType]) && empty($values['options'][$attributeControlTypeId])) {
                        $values['options'][$attributeControlTypeId] = $attributeControlTypeValue;
                        $doSave = true;
                    }
                    if (!empty($values['super_attribute'][$attributeSystemType]) && empty($values['super_attribute'][$attributeControlTypeId])) {
                        $values['super_attribute'][$attributeControlTypeId] = $attributeControlTypeValue;
                        $doSave = true;
                    }

                    if ($doSave === true) {
                        $quote->updateItem($item->getId(), $values);
                    }
                }
                if(!empty($items)){
                    $this->quoteRepository->save($quote);
                }
            } catch (\Exception $e) {
                print($e->getMessage());
            }
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
