<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Claim\PlaceOrder;

use GoMage\Samples\Api\Data\Claim\InfoInterface;
use GoMage\Samples\Model\Claim\PlaceOrder\CustomerCreator\Registry;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\AccountManagement;
use Magento\Store\Model\StoreManagerInterface;

class CustomerCreator
{
    private StoreManagerInterface $storeManager;
    private CustomerInterfaceFactory $customerFactory;
    private AccountManagement $accountManagement;
    private Registry $registry;

    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerFactory,
        AccountManagement $accountManagement,
        Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->accountManagement = $accountManagement;
        $this->registry = $registry;
    }

    public function create(InfoInterface $info): ?CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customerFactory->create();

        $customer
            ->setWebsiteId($this->storeManager->getStore()->getWebsiteId())
            ->setStoreId($this->storeManager->getStore()->getId())
            ->setFirstname($info->getFirstname())
            ->setLastname($info->getLastname())
            ->setEmail($info->getEmail())
            ->setPrefix($info->getPrefix());

        $this->registry->addCustomer($info->getEmail());

        return $this->accountManagement->createAccount($customer, $info->getPassword());
    }
}
