<?php declare(strict_types=1);

namespace Smartblinds\Mollie\Service\Order\Uncancel;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterfaceFactory;

class OrderReservation extends \Mollie\Payment\Service\Order\Uncancel\OrderReservation
{
    private WebsiteRepositoryInterface $websiteRepository;
    private Processor $priceIndexer;
    private SalesChannelInterfaceFactory $salesChannelFactory;
    private SalesEventInterfaceFactory $salesEventFactory;
    private PlaceReservationsForSalesEventInterfaceFactory $placeReservationsForSalesEventFactory;
    private ItemToSellInterfaceFactory $itemToSellFactory;

    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        Processor $priceIndexer,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        PlaceReservationsForSalesEventInterfaceFactory $placeReservationsForSalesEventFactory,
        ItemToSellInterfaceFactory $itemToSellFactory
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->priceIndexer = $priceIndexer;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->placeReservationsForSalesEventFactory = $placeReservationsForSalesEventFactory;
        $this->itemToSellFactory = $itemToSellFactory;
    }

    public function execute(OrderItemInterface $orderItem)
    {
        $websiteId = $orderItem->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);

        $salesEvent = $this->salesEventFactory->create([
            'type' => 'order_uncanceled',
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string)$orderItem->getOrderId(),
        ]);

        $placeReservationsForSalesEvent = $this->placeReservationsForSalesEventFactory->create();
        $placeReservationsForSalesEvent->execute($this->getItemsToUncancel($orderItem), $salesChannel, $salesEvent);

        $this->priceIndexer->reindexRow($orderItem->getProductId());
    }

    private function getItemsToUncancel(OrderItemInterface $orderItem)
    {
        $itemsToUncancel = [];
        $itemsToUncancel[] = $this->itemToSellFactory->create([
            'sku' => $orderItem->getSku(),
            // 0 - X = make it negative.
            'qty' => 0 - $orderItem->getQtyCanceled(),
        ]);

        if ($orderItem->getHasChildren()) {
            foreach ($itemsToUncancel as $item) {
                $itemsToUncancel[] = $this->itemToSellFactory->create([
                    'sku' => $orderItem->getSku(),
                    // 0 - X = make it negative.
                    'qty' => 0 - $orderItem->getQtyCanceled(),
                ]);
            }
        }

        return $itemsToUncancel;
    }
}
