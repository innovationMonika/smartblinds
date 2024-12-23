<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Hover implements OptionSourceInterface, \Magento\Framework\Option\ArrayInterface
{
    public const SCALE = 0;

    public const DESCRIPTION = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SCALE, 'label' => __('Scale Photo')],
            ['value' => self::DESCRIPTION, 'label' => __('Show Photo Description')]
        ];
    }
}
