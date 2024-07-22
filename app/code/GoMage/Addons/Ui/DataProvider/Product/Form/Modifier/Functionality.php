<?php
namespace GoMage\Addons\Ui\DataProvider\Product\Form\Modifier;

use GoMage\Addons\Model\Category\Attribute\Source\Page;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use GoMage\Addons\Helper\Data;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Field;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Data provider for attraction highlights field
 */
class Functionality extends AbstractModifier
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    protected $scopeName;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param Page $page
     * @param string $scopeName
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        Page $page,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
        $this->page = $page;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        $this->setFunctionalityData($model, $data, Data::FUNC_SYSTEEM_ROWS);
        $this->setFunctionalityData($model, $data, Data::FUNC_STOF_ROWS);
        $this->setFunctionalityData($model, $data, Data::FUNC_GARANTIE_ROWS);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->initFunctionalityFields(Data::FUNC_SYSTEEM_ROWS, 'Systeem');
        $this->initFunctionalityFields(Data::FUNC_STOF_ROWS, 'Stof');
        $this->initFunctionalityFields(Data::FUNC_GARANTIE_ROWS, 'Garantie');
        return $this->meta;
    }

    /**
     * Customize attraction highlights field
     *
     * @return $this
     */
    protected function initFunctionalityFields($index, $fieldName)
    {
        $fieldPath = $this->arrayManager->findPath(
            $index,
            $this->meta,
            null,
            'children'
        );

        if ($fieldPath) {
            $this->meta = $this->arrayManager->merge(
                $fieldPath,
                $this->meta,
                $this->initFunctionalityFieldStructure($fieldPath, $fieldName, $index)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($fieldPath, 0, -3)
                . '/' . $index,
                $this->meta,
                $this->arrayManager->get($fieldPath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($fieldPath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }


    /**
     * Get attraction highlights dynamic rows structure
     *
     * @param string $fieldPath
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function initFunctionalityFieldStructure($fieldPath, $fieldName, $index)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __($fieldName),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($fieldPath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'block' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'select',
                                        'componentType' => Field::NAME,
                                        'dataType' => 'text',
                                        'label' => __('CMS Block'),
                                        'dataScope' => 'block',
                                        'require' => '1',
                                        'options' => $this->page->getAllOptions(),
                                        'elementTmpl' => 'ui/form/element/select',
                                    ],
                                ],
                            ],
                        ],

                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param ProductInterface $model
     * @param array $data
     * @param string $key
     */
    protected function setFunctionalityData(ProductInterface $model, array &$data, string $key)
    {
        $rowsData = $model->getData($key);

        if ($rowsData) {
            $rowsData = json_decode($rowsData, true);
            $path = $model->getId() . '/' . self::DATA_SOURCE_DEFAULT . '/'. $key;
            $data = $this->arrayManager->set($path, $data, $rowsData);
        }
    }
}
