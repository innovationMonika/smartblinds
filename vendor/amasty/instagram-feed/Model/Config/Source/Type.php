<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface, \Magento\Framework\Option\ArrayInterface
{
    public const GRID = 0;

    public const SLIDER = 1;

    public const SINGLE = 2;

    public const COLLAGE = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::GRID, 'label' => __('Grid')],
            ['value' => self::SLIDER, 'label' => __('Slider')],
            ['value' => self::SINGLE, 'label' => __('Single Post')],
            ['value' => self::COLLAGE, 'label' => __('Collage')]
        ];
    }
}
