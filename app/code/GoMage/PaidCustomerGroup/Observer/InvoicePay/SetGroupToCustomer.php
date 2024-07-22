<?php declare(strict_types=1);

namespace GoMage\PaidCustomerGroup\Observer\InvoicePay;

use GoMage\PaidCustomerGroup\Model\GroupSetter;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;

class SetGroupToCustomer implements ObserverInterface
{
    private GroupSetter $groupSetter;

    public function __construct(GroupSetter $groupSetter)
    {
        $this->groupSetter = $groupSetter;
    }

    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        $this->groupSetter->setByInvoice($invoice);
    }
}
