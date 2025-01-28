<?php

namespace Smartblinds\Options\Model\Attribute\OptionValue;

use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class ValueWidth extends AbstractAttribute
{
    public function getName()
    {
        return 'value_code_width';
    }

    public function prepareOptionsMageOne($systemData, $productData, $optionData, &$preparedOptionData, $valueData = [], &$preparedValueData = [])
    {
        $preparedValueData[static::getName()] = 0;
    }

    public function prepareImportDataMageTwo($data, $type)
    {
        return empty($data['custom_option_row_' . $this->getName()])
            ? 0
            : $data['custom_option_row_' . $this->getName()];
    }
}
