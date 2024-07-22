<?php
/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class TreeSourceOptionsSelect implements ArrayInterface
{

    /**
     * type source tree
     */
    const ROOT = 0;
    const CHILDREN = 1;
    const SIBLINGS = 2;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ROOT, 'label' => __('Root')],
            ['value' => self::CHILDREN, 'label' => __('Children')],
            ['value' => self::SIBLINGS, 'label' => __('Siblings')]
        ];
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->getOptionArray();
    }
}
