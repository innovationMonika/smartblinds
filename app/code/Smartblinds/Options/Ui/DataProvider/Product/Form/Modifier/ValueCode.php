<?php declare(strict_types=1);

namespace Smartblinds\Options\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\App\Request\Http;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface;
use Smartblinds\Options\Model\Attribute\Option\ModalCode as ModalCodeOption;

class ValueCode extends AbstractModifier implements ModifierInterface
{
    const FIELD_VALUE_CODE_NAME = 'value_code';

    private Http $request;

    private $meta = [];

    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    public function getSortOrder()
    {
        return 102;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        if ($this->request->getRouteName() == 'mageworx_optiontemplates') {
            $this->form = 'mageworx_optiontemplates_group_form';
        }

        $this->addSetting();

        return $this->meta;
    }

    protected function addSetting()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        // Add fields to the values
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'],
            $this->getValueFieldsConfig()
        );

        // Add fields to the option
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $this->getOptionFieldsConfig()
        );
    }

    protected function getOptionFieldsConfig()
    {
        $fields[self::FIELD_VALUE_CODE_NAME] = $this->getSortOrderConfig(41);

        return $fields;
    }

    protected function getValueFieldsConfig()
    {
        $fields[self::FIELD_VALUE_CODE_NAME] = $this->getSortOrderConfig(51);

        return $fields;
    }

    protected function getSortOrderConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'             => __('Value Code'),
                        'componentType'     => Field::NAME,
                        'formElement'       => Input::NAME,
                        'dataScope'         => self::FIELD_VALUE_CODE_NAME,
                        'dataType'          => Text::NAME,
                        'visible'           => true,
                        'additionalClasses' => 'mageworx-width-125',
                        'sortOrder'         => $sortOrder
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
