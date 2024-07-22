<?php

namespace Smartblinds\Checkout\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OrderTypes implements OptionSourceInterface
{
    const ORDER_TYPE_REGULAR  = 'REGULAR';
    const ORDER_TYPE_WARRANTY = 'WARRANTY';

    public function toOptionArray()
    {
        return [
            [
                'label' => self::ORDER_TYPE_REGULAR,
                'value' => self::ORDER_TYPE_REGULAR
            ],
            [
                'label' => self::ORDER_TYPE_WARRANTY,
                'value' => self::ORDER_TYPE_WARRANTY
            ]
        ];
    }

    public function getValues()
    {
        return [
            self::ORDER_TYPE_REGULAR,
            self::ORDER_TYPE_WARRANTY
        ];
    }
}
