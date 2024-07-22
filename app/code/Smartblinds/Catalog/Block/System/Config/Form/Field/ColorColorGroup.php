<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\Catalog\Block\System\Config\Form\Field\Column\Color;
use Smartblinds\Catalog\Block\System\Config\Form\Field\Column\ColorGroup;

class ColorColorGroup extends AbstractFieldArray
{
    private Color $colorRenderer;
    private ColorGroup $colorGroupRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('color_group', [
            'label' => __('Color Group'),
            'renderer' => $this->getColorGroupRenderer()
        ]);
        $this->addColumn('color', [
            'label' => __('Color'),
            'renderer' => $this->getColorRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $color = $row->getColor();
        if ($color !== null) {
            $options['option_' . $this->getColorRenderer()
                ->calcOptionHash($color)] = 'selected="selected"';
        }

        $colorGroup = $row->getColorGroup();
        if ($colorGroup !== null) {
            $options['option_' . $this->getColorGroupRenderer()
                ->calcOptionHash($colorGroup)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getColorRenderer()
    {
        if (!isset($this->colorRenderer)) {
            $this->colorRenderer = $this->getLayout()->createBlock(
                Color::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->colorRenderer;
    }

    private function getColorGroupRenderer()
    {
        if (!isset($this->colorGroupRenderer)) {
            $this->colorGroupRenderer = $this->getLayout()->createBlock(
                ColorGroup::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->colorGroupRenderer;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $style = <<<STYLE
<style>
    #row_smartblinds_catalog_color_group_config .label { display: none; }
    #row_smartblinds_catalog_color_group_config .value { width: 100% }
</style>
STYLE;
        return $style . $html;
    }
}
