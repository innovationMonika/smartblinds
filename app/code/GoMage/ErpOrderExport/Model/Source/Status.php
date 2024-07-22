<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public const UNEXPORTED = 0;
    public const ACCEPTED   = 1;
    public const REJECTED   = 2;

    public function toOptionArray()
    {
        return [
            [
                'label' => __('Not Exported'),
                'value' => self::UNEXPORTED
            ],
            [
                'label' => __('Accepted'),
                'value' => self::ACCEPTED
            ],
            [
                'label' => __('Rejected'),
                'value' => self::REJECTED
            ]
        ];
    }
}
