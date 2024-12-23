<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CollageSize implements OptionSourceInterface, \Magento\Framework\Option\ArrayInterface
{
    public const THREE = 3;

    public const FOUR = 4;

    public const FIVE = 5;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::THREE, 'label' => '3 x 3'],
            ['value' => self::FOUR, 'label' => '4 x 4'],
            ['value' => self::FIVE, 'label' => '5 x 5']
        ];
    }
}
