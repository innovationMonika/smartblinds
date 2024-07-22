<?php

namespace Smartblinds\AutoInvoice\Model;

use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Service\InvoiceServiceFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;
use Smartblinds\Checkout\Model\Config as CheckoutConfig;

class InvoiceProcessor
{
    private OrderCollectionFactory $orderCollectionFactory;
    private TransactionFactory $transactionFactory;
    private InvoiceServiceFactory $invoiceServiceFactory;
    private LoggerInterface $logger;
    private CheckoutConfig $checkoutConfig;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        TransactionFactory $transactionFactory,
        InvoiceServiceFactory $invoiceServiceFactory,
        LoggerInterface $logger,
        CheckoutConfig $checkoutConfig
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceServiceFactory = $invoiceServiceFactory;
        $this->logger = $logger;
        $this->checkoutConfig = $checkoutConfig;
    }

    public function getOrdersToProcess()
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('status', ['nin' =>
                Order::STATE_COMPLETE,
                Order::STATE_CANCELED,
                Order::STATE_CLOSED,
                Order::STATE_HOLDED]
            )
            ->addFieldToFilter('customer_email', ['in' => $this->checkoutConfig->getOrderTypeChoosingEmails()])
            ->addFieldToFilter('total_invoiced', ['null' => true]);
        return $collection->getItems();
    }

    public function invoice(Order $order)
    {
        $invoice = $this->invoiceServiceFactory->create()
            ->prepareInvoice($order);
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();

        $order->setState('processing');
        $order->setStatus('processing');

        $transactionSave = $this->transactionFactory->create()
            ->addObject($invoice)
            ->addObject($order);

        $transactionSave->save();
    }
}
