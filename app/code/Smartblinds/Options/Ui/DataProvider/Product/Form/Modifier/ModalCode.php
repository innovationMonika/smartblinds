<?php declare(strict_types=1);

namespace Smartblinds\Options\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface;
use Smartblinds\Options\Model\Attribute\Option\ModalCode as ModalCodeOption;

class ModalCode extends AbstractModifier implements ModifierInterface
{
    private $meta = [];

    public function getSortOrder()
    {
        return 101;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->addSetting();

        return $this->meta;
    }

    protected function addSetting()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        $fields = [ModalCodeOption::CODE => $this->getFieldConfig()];
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $fields
        );
    }

    protected function getFieldConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Modal Code'),
                        'component' => 'Magento_Catalog/component/static-type-input',
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataType'      => Text::NAME,
                        'dataScope'     => ModalCodeOption::CODE
                    ],
                ],
            ],
        ];
    }

    public function isProductScopeOnly()
    {
        return false;
    }
}
