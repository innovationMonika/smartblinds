<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Color;

class SystemColor extends AbstractFieldArray
{
    private Color $renderer;

    protected function _prepareToRender()
    {
        $this->addColumn('value', [
            'label' => __('Value')
        ]);
        $this->addColumn('option', [
            'label' => __('Size'),
            'renderer' => $this->getRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $option = $row->getOption();
        if ($option !== null) {
            $options['option_' . $this->getRenderer()
                ->calcOptionHash($option)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getRenderer()
    {
        if (!isset($this->renderer)) {
            $this->renderer = $this->getLayout()->createBlock(
                Color::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->renderer;
    }
}
