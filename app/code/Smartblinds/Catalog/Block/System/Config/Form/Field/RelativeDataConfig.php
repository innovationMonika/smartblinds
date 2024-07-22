<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\Catalog\Block\System\Config\Form\Field\Column\Color;
use Smartblinds\Catalog\Block\System\Config\Form\Field\Column\Transparency;
use Smartblinds\Catalog\Block\System\Config\Form\Field\Column\FabricSize;

class RelativeDataConfig extends AbstractFieldArray
{
    private Color $colorRenderer;
    private Transparency $transparencyRenderer;
    private FabricSize $fabricSizeRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('family_sku', [
            'label' => __('Family Sku')
        ]);
        $this->addColumn('transparency', [
            'label' => __('Transparency'),
            'renderer' => $this->getTransparencyRenderer()
        ]);
        $this->addColumn('color', [
            'label' => __('Color'),
            'renderer' => $this->getColorRenderer()
        ]);
        $this->addColumn('fabric_size', [
            'label' => __('Fabric Size'),
            'renderer' => $this->getFabricSizeRenderer()
        ]);
        $this->addColumn('smartblinds_sku', [
            'label' => __('Smartblinds Sku')
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

        $transparency = $row->getTransparency();
        if ($transparency !== null) {
            $options['option_' . $this->getTransparencyRenderer()
                ->calcOptionHash($transparency)] = 'selected="selected"';
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

    private function getTransparencyRenderer()
    {
        if (!isset($this->transparencyRenderer)) {
            $this->transparencyRenderer = $this->getLayout()->createBlock(
                Transparency::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->transparencyRenderer;
    }

    private function getFabricSizeRenderer()
    {
        if (!isset($this->fabricSizeRenderer)) {
            $this->fabricSizeRenderer = $this->getLayout()->createBlock(
                FabricSize::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->fabricSizeRenderer;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $style = <<<STYLE
<style>
    #row_smartblinds_catalog_relative_data_config .label { display: none; }
    #row_smartblinds_catalog_relative_data_config .value { width: 100% }
</style>
STYLE;
        return $style . $html;
    }
}
