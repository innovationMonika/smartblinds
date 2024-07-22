<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderData;

use GoMage\ErpOrderExport\Model\OrderData\Items\OptionsCollector;
use Magento\Framework\Escaper;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Smartblinds\System\Model\Product\Attribute\Source\SystemCategory;

class Items implements DataProviderInterface
{
    protected LoggerInterface $logger;
    protected Escaper $escaper;
    private OptionsCollector $optionsCollector;

    public function __construct(
        OptionsCollector $attributesCollector,
        LoggerInterface $logger,
        Escaper $escaper
    ) {
        $this->optionsCollector = $attributesCollector;
        $this->logger = $logger;
        $this->escaper = $escaper;
    }

    public function _getBaseTotalAmount($item)
    {
        $baseTotalAmount =  $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
        return $baseTotalAmount;
    }

    public function getData(OrderInterface $order): array
    {
        $items = [];
        $i = 0;
        foreach ($order->getItems() as $orderItem) {

            if ($orderItem->getParentItemId()) {
                continue;
            }

            if ($orderItem->getProductType() === 'simple' && $orderItem->getSku() !== 'curtain_tracks') {
                $items[] = [
                    'id'       => $orderItem->getItemId(),
                    'quantity' => round((float)$orderItem->getQtyOrdered()),
                    'category' => 'accessories',
                    'salesPrice' => $this->_getBaseTotalAmount($orderItem),
                    'name'     => '',
                    'options'  => [['name' => 'accessory_sku', 'value' => $orderItem->getSku()]],
                ];
                continue;
            }

            $options = $this->optionsCollector->getOptions($orderItem, $i);
            $i++;

            $curtainTracksAccessories = [];
            if ($orderItem->getSku() === 'curtain_tracks' && isset($options['curtain_tracks_accessories'])) {
                $curtainTracksAccessories = $options['curtain_tracks_accessories'];
                unset($options['curtain_tracks_accessories']);
            }

            $items[] = [
                'id' => $orderItem->getItemId(),
                'quantity' => round((float)$orderItem->getQtyOrdered()),
                'category' => $this->getItemCategory($orderItem),
                'salesPrice' => $this->_getBaseTotalAmount($orderItem),
                'name'     => str_replace(" ", "_", ($orderItem->getReference() ?? '')),
                'options'  => $options,
            ];

            if (!empty($curtainTracksAccessories)) {
                foreach ($curtainTracksAccessories as $accessorySku) {
                    $items[] = [
                        'quantity' => round((float)$orderItem->getQtyOrdered()),
                        'category' => 'accessories',
                        'name'     => '',
                        'options'  => [['name' => 'accessory_sku', 'value' => $accessorySku]],
                    ];
                }
            }
        }

        return ['items' => $items];
    }

    private function getItemCategory($orderItem)
    {
        if ($orderItem->getSku() === 'curtain_tracks') {
            return 'curtain_track';
        }
        $name = $orderItem->getSystemCategory();
        if ($name === SystemCategory::ROLLER_TITLE) {
            return 'roller_blinds';
        }
        if ($name === SystemCategory::DUOROLLER_TITLE) {
            return 'double_roller_blinds';
        }
        if ($name === SystemCategory::VENETIAN_TITLE) {
            return SystemCategory::VENETIAN;
        }
        if ($name === SystemCategory::HONEYCOMB_TITLE) {
            return SystemCategory::HONEYCOMB;
        }
        return null;
    }
}
