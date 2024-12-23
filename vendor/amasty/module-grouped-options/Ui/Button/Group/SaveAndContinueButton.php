<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Grouped Options for Magento 2
*/

declare(strict_types=1);

namespace Amasty\GroupedOptions\Ui\Button\Group;

class SaveAndContinueButton extends GenericButton
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'on_click'  => '',
            'sort_order' => 40,
        ];
    }
}