<?php
/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class DefaultRouteBehavior implements ArrayInterface
{

    /**
     * type source tree
     */
    const NO_CHANGES = 0;
    const PERMANENT_REDIRECT = 1;
    const NOTFOUND = 2;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NO_CHANGES, 'label' => __('No Changes')],
            ['value' => self::PERMANENT_REDIRECT, 'label' => __('301 Redirect')],
            ['value' => self::NOTFOUND, 'label' => __('Page Not Found')]
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
