<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\Config\Source;

/**
 * Store Views
 */
class Store extends \Magento\Store\Ui\Component\Listing\Column\Store\Options
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = parent::toOptionArray();
        array_unshift($this->options, ['value' => 0, 'label' => __('No x-default value')]);
        return $this->options;
    }
}