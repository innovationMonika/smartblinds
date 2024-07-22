<?php declare(strict_types=1);

namespace Smartblinds\Options\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\App\Request\Http;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Field;
use MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface;
use Smartblinds\Options\Model\Attribute\Option\MoveToTop;

class Top extends AbstractModifier implements ModifierInterface
{
    private $meta = [];

    public function getSortOrder()
    {
        return 99;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->addTopSetting();

        return $this->meta;
    }

    protected function addTopSetting()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

        $fields = [MoveToTop::CODE => $this->getTopFieldConfig()];
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $fields
        );
    }

    protected function getTopFieldConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Move To Top'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => MoveToTop::CODE,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => 99,
                        'prefer'        => 'toggle',
                        'valueMap'      => [
                            'true'  => '1',
                            'false' => '0',
                        ],
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
