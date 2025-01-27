<?php

declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\OrderData\Items;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Smartblinds\System\Model\Product\Attribute\Source\SystemCategory;

class OptionsCollector
{
    public function getOptions(OrderItemInterface $orderItem, $number)
    {
        if ($orderItem->getProductType() === 'configurable' && (!$orderItem->getWidth() || !$orderItem->getHeight())) {
            throw new LocalizedException(__('Product does not have width and height'));
        }

        $systemType = $this->getSystemType($orderItem);

        $result = [
            ['name' => 'control_type', 'value' => $this->getControlType($orderItem)],
            ['name' => 'width', 'value' => $this->getWidth($orderItem)],
            ['name' => 'mount', 'value' => $orderItem->getData('montage')]
        ];

        $inProhibitedCategories = in_array($orderItem->getSystemCategory(), [
            SystemCategory::VENETIAN_TITLE,
            SystemCategory::HONEYCOMB_TITLE
        ]);
        $isVenetianSystemCategory = $orderItem->getSystemCategory() === SystemCategory::VENETIAN_TITLE;
        $isCurtainTracks = $orderItem->getSku() === 'curtain_tracks';

        if (!$inProhibitedCategories && !$isCurtainTracks) {
            $result[] = ['name' => 'system_size', 'value' => $this->getSystemSize($orderItem)];
        }

        if ($isCurtainTracks) {
            $result[] = ['name' => 'montage_side', 'value' => $orderItem->getData('wall_or_ceiling_fitting')];
            $result[] = ['name' => 'system_type', 'value' => $orderItem->getData('curtain_type')];
            if ($accessories = $this->getCurtainTracksAccessories($orderItem)) {
                $result['curtain_tracks_accessories'] = $accessories;
            }
        } else {
            $result[] = ['name' => 'height', 'value' => $orderItem->getHeight()];
            $result[] = ['name' => 'software', 'value' => $orderItem->getSoftware()];
            if (!$isVenetianSystemCategory) {
                $result[] = ['name' => 'system_type', 'value' => $systemType];
                $result[] = ['name' => 'system_color', 'value' => $this->systemColorMapping($orderItem)];
            }
        }

        if ($systemType !== 'tdbu') {
            $result[] = ['name' => 'operating_side', 'value' => $orderItem->getMotorSide()];
        }

        if (strtolower($orderItem->getControlType() ?? '') == 'chain') {
            $result[] = ['name' => 'mounting_height', 'value' => (string)max($orderItem->getHeight(), 2000)];
        }

        if ($number == 0 && $orderItem->getSku() !== 'curtain_tracks') {
            $result[] = ['name' => 'accessories', 'value' => 'usbc.hon'];
        }

        $motorValue = $orderItem->getData('motor') ?? $this->getDefaultMotorValue($orderItem);
        if ($motorValue) {
            $result[] = ['name' => 'motor', 'value' => $motorValue];
        }

        if($orderItem->getData('bottombar')){
            $result[] = ['name' => 'bottombar', 'value' => $orderItem->getData('bottombar')];
        }
        if($orderItem->getData('side_span')){
            $result[] = ['name' => 'side_span', 'value' => $orderItem->getData('side_span')];
        }
        if($orderItem->getData('clamp')){
            $result[] = ['name' => 'clamp', 'value' => $orderItem->getData('clamp')];
        }
        if ($orderItem->getProductType() === 'simple' && $orderItem->getSku() !== 'curtain_tracks'){
             $result[] = ['name' => 'accessory_sku', 'value' => $orderItem->getData('accessory_sku')];
        }

        return $result;
    }

    private function getDefaultMotorValue($orderItem)
    {
        if ($orderItem->getSku() === 'curtain_tracks') {
            return 'coulisse-cm-36';
        }
        if ($systemCategory = $orderItem->getSystemCategory()) {
            switch ($systemCategory) {
                case SystemCategory::ROLLER_TITLE:
                case SystemCategory::DUOROLLER_TITLE:
                    return 'coulisse-cm-03-e';
                case SystemCategory::HONEYCOMB_TITLE:
                    return 'coulisse-cm-08-e';
                case SystemCategory::VENETIAN_TITLE:
                    return 'coulisse-cm-06-e-v';
            }
        }
        return false;
    }

    private function getWidth($orderItem)
    {
        return $orderItem->getData('curtain_tracks_width') ?
            $orderItem->getData('curtain_tracks_width') * 10
            : $orderItem->getWidth();
    }

    private function getCurtainTracksAccessories($orderItem)
    {
        $accessories = [];
        $wifi = strtolower($orderItem->getData('motion_blinds_wifi_bridge') ?? '');
        if (in_array($wifi, ['yes'])) {
            $accessories[] = 'cm-20';
        }
        $remoteControl = strtolower($orderItem->getData('remote_control') ?? '');
        if (in_array($remoteControl, ['yes'])) {
            $accessories[] = 'cm-12';
        }
        return $accessories;
    }

    private function getControlType($orderItem)
    {
        if ($orderItem->getSku() === 'curtain_tracks') {
            return 'motor';
        }
        return $orderItem->getControlType() ? strtolower($orderItem->getControlType() ?? '') : null;
    }

    private function getSystemSize($orderItem)
    {
        return $orderItem->getSystemSize() ? strtolower($orderItem->getSystemSize() ?? '') : null;
    }

    private function getSystemType($orderItem)
    {
        if (!$orderItem->getSystemType()) {
            return null;
        }
        $type = strtolower($orderItem->getSystemType() ?? '');
        if ($type === 'brackets') {
            return 'open';
        }
        if ($type === 'cassette') {
            return 'semi-open';
        }
        return $type;
    }

    private function systemColorMapping($orderItem)
    {
        $color = strtolower($orderItem->getSystemColor() ?? '');
        $isHoneycomb = $orderItem->getSystemCategory() === SystemCategory::HONEYCOMB_TITLE;
        if ($isHoneycomb) {
            $map = ['grey' => 'silver'];
        }
        $isRollerCategories = in_array($orderItem->getSystemCategory(), ['Roller', 'Duo Roller']);
        if ($isRollerCategories) {
            $map = ['grey' => 'anodized'];
        }
        return $map[$color] ?? $color;
    }
}
