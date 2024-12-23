<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PostSize implements OptionSourceInterface, \Magento\Framework\Option\ArrayInterface
{
    public const THUMBNAIL = 'thumbnail';
    public const THUMBNAIL_SIZE = 150;

    public const LOW_RESOLUTION = 'low_resolution';
    public const LOW_RESOLUTION_SIZE = 320;

    public const STANDARD_RESOLUTION = 'standard_resolution';
    public const STANDARD_RESOLUTION_SIZE = 640;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::THUMBNAIL, 'label' => __('Thumbnail (150x150)')],
            ['value' => self::LOW_RESOLUTION, 'label' => __('Low Resolution (320x320)')],
            ['value' => self::STANDARD_RESOLUTION, 'label' => __('Standard Resolution (640x640)')]
        ];
    }
}
