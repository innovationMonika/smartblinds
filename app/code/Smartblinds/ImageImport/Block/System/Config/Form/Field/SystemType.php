<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Type;

class SystemType extends AbstractFieldArray
{
    private Type $typeRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('value', [
            'label' => __('Value')
        ]);
        $this->addColumn('option', [
            'label' => __('Type'),
            'renderer' => $this->getTypeRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $option = $row->getOption();
        if ($option !== null) {
            $options['option_' . $this->getTypeRenderer()
                ->calcOptionHash($option)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getTypeRenderer()
    {
        if (!isset($this->typeRenderer)) {
            $this->typeRenderer = $this->getLayout()->createBlock(
                Type::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->typeRenderer;
    }
}
