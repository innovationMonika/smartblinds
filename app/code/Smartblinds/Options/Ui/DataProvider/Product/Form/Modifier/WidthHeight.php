<?php declare(strict_types=1);

namespace Smartblinds\Options\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Smartblinds\Options\Model\Product\Option\Type\WidthHeight as WidthHeightType;

class WidthHeight extends AbstractModifier
{
    private $meta = [];

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->addWidthHeightOption();

        return $this->meta;
    }

    protected function addWidthHeightOption()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        $fields = [CustomOptions::FIELD_TYPE_NAME => $this->getWidthHeightOptionConfig()];
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $fields
        );
    }

    protected function getWidthHeightOptionConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'groupsConfig' => [
                            WidthHeightType::GROUP_CODE => [
                                'values' => [WidthHeightType::TYPE_CODE]
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }
}
