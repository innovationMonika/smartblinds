<?php
namespace Smartblinds\Mollie\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Mollie\Payment\Helper\General as MollieHelper;
use Mollie\Payment\Model\OrderLinesFactory;
use Mollie\Payment\Model\ResourceModel\OrderLines\CollectionFactory as OrderLinesCollectionFactory;
use Mollie\Payment\Service\Order\Creditmemo as CreditmemoService;
use Mollie\Payment\Service\Order\Lines\Order as OrderOrderLines;

class OrderLines extends \Mollie\Payment\Model\OrderLines
{
    /**
     * @var MollieHelper
     */
    private $mollieHelper;

    /**
     * @var OrderLinesCollectionFactory
     */
    private $orderLinesCollection;

    /**
     * @param MollieHelper $mollieHelper
     * @param OrderLinesFactory $orderLinesFactory
     * @param OrderLinesCollectionFactory $orderLinesCollection
     * @param Context $context
     * @param Registry $registry
     * @param CreditmemoService $creditmemoService
     * @param OrderOrderLines $orderOrderLines
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        MollieHelper $mollieHelper,
        OrderLinesFactory $orderLinesFactory,
        OrderLinesCollectionFactory $orderLinesCollection,
        Context $context,
        Registry $registry,
        CreditmemoService $creditmemoService,
        OrderOrderLines $orderOrderLines,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mollieHelper = $mollieHelper;
        $this->orderLinesCollection = $orderLinesCollection;
        parent::__construct($mollieHelper, $orderLinesFactory, $orderLinesCollection, $context, $registry,
            $creditmemoService, $orderOrderLines, $resource, $resourceCollection, $data);
    }


    /**
     * @param ShipmentInterface $shipment
     * @return array
     */
    public function getShipmentOrderLines(ShipmentInterface $shipment): array
    {
        $orderLines = [];

        /** @var OrderInterface $order */
        $order = $shipment->getOrder();
        $orderHasDiscount = abs($order->getDiscountAmount() ?? 0) > 0;

        /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
        foreach ($shipment->getItemsCollection() as $item) {
            if (!$item->getQty()) {
                continue;
            }

            $orderItemId = $item->getOrderItemId();
            $lineId = $this->getOrderLineByItemId($orderItemId)->getLineId();
            $line = ['id' => $lineId, 'quantity' => $item->getQty()];

            if ($orderHasDiscount) {
                $orderItem = $item->getOrderItem();

                $rowTotal = $orderItem->getBaseRowTotal()
                    - $orderItem->getBaseDiscountAmount()
                    + $orderItem->getBaseTaxAmount()
                    + $orderItem->getBaseDiscountTaxCompensationAmount();

                $line['amount'] = $this->mollieHelper->getAmountArray(
                    $order->getBaseCurrencyCode(),
                    (($rowTotal) / $orderItem->getQtyOrdered()) * $item->getQty()
                );
            }

            $orderLines[] = $line;
        }

        if ($order->getShipmentsCollection()->count() === 0) {
            $this->addNonProductItems($order, $orderLines);
        }

        return ['lines' => $orderLines];
    }

    private function addNonProductItems(OrderInterface $order, array &$orderLines): void
    {
        $collection = $this->orderLinesCollection->create()
            ->addFieldToFilter('order_id', ['eq' => $order->getEntityId()])
            ->addFieldToFilter('type', ['nin' => ['physical', 'digital']]);

        /** @var \Mollie\Payment\Model\OrderLines $item */
        foreach ($collection as $item) {
            $orderLines[] = [
                'id' => $item->getLineId(),
                'quantity' => 1,
            ];
        }
    }
}
