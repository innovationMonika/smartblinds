<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Sorting implements OptionSourceInterface, \Magento\Framework\Option\ArrayInterface
{
    public const NEWEST = 0;

    public const LIKED = 1;

    public const COMMENTED = 2;

    public const RANDOM = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NEWEST, 'label' => __('Newest')],
            ['value' => self::LIKED, 'label' => __('Most Liked')],
            ['value' => self::COMMENTED, 'label' => __('Most Commented')],
            ['value' => self::RANDOM, 'label' => __('Random')]
        ];
    }
}
