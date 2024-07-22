<?php declare(strict_types=1);

namespace GoMage\PaidCustomerGroup\Model;

use Exception;
use GoMage\PaidCustomerGroup\Model\ResourceModel\CustomerPaidTotal;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Customer\Api\GroupRepositoryInterface;
use Psr\Log\LoggerInterface;

class GroupSetter
{
    private Config $config;
    private CustomerRepositoryInterface $customerRepository;
    private GroupManagementInterface $groupManagement;
    private GroupRepositoryInterface $groupRepository;
    private CustomerPaidTotal $customerPaidTotal;
    private LoggerInterface $logger;

    public function __construct(
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        GroupManagementInterface $groupManagement,
        GroupRepositoryInterface $groupRepository,
        CustomerPaidTotal $customerPaidTotal,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->groupManagement = $groupManagement;
        $this->groupRepository = $groupRepository;
        $this->customerPaidTotal = $customerPaidTotal;
        $this->logger = $logger;
    }

    public function setByInvoice(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $customerId = $order->getCustomerId();
        $groupId = $this->config->getGroupId();

        if (!$this->config->isEnabled() || !$customerId || !$groupId) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
            $defaultGroupId = $this->groupManagement->getDefaultGroup($customer->getStoreId())->getId();
            $totalPaid = $this->customerPaidTotal->loadAmount((int)$customerId) + $invoice->getBaseGrandTotal();
            if ($customer->getGroupId() == $defaultGroupId && $totalPaid > $this->config->getAmount()) {
                $group = $this->groupRepository->getById($groupId);
                $customer->setGroupId($group->getId());
                $this->customerRepository->save($customer);
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
