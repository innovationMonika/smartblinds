<?php declare(strict_types=1);

namespace Smartblinds\Options\Model\Attribute\Option;

use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class OptionCode extends AbstractAttribute
{
    const CODE = 'option_code';

    const FIELD_MAGE_ONE_OPTIONS_IMPORT = '_custom_option_customoptions_option_code';

    public function getName()
    {
        return self::CODE;
    }

    public function importTemplateMageOne($data)
    {
        return $data["customoptions_{$this->getName()}"] ?? 0;
    }

    public function prepareOptionsMageOne(
        $systemData,
        $productData,
        $optionData,
        &$preparedOptionData,
        $valueData = [],
        &$preparedValueData = []
    ) {
        if (!isset($optionData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])) {
            return;
        }
        $preparedOptionData[static::getName()] = (int)$optionData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT];
    }
}
