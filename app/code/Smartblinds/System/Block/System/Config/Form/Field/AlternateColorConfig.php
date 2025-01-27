<?php declare(strict_types=1);

namespace Smartblinds\System\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\System\Block\System\Config\Form\Field\Column\AlternateColor;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Category;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Color;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Type;

class AlternateColorConfig extends AbstractFieldArray
{
    private Category $systemCategoryRenderer;
    private Type $systemTypeRenderer;
    private Color $systemColorRenderer;
    private AlternateColor $alternateSystemColorRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('system_category', [
            'label' => __('System Category'),
            'renderer' => $this->getSystemCategoryRenderer()
        ]);
        $this->addColumn('system_type', [
            'label' => __('System Type'),
            'renderer' => $this->getSystemTypeRenderer()
        ]);
        $this->addColumn('option', [
            'label' => __('System Color'),
            'renderer' => $this->getSystemColorRenderer()
        ]);
        $this->addColumn('option_alternate', [
            'label' => __('Alternate System Color'),
            'renderer' => $this->getAlternateSystemColorRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $systemCategory = $row->getSystemCategory();
        if ($systemCategory !== null) {
            $options['option_' . $this->getSystemCategoryRenderer()
                ->calcOptionHash($systemCategory)] = 'selected="selected"';
        }

        $systemType = $row->getSystemType();
        if ($systemType !== null) {
            $options['option_' . $this->getSystemTypeRenderer()
                ->calcOptionHash($systemType)] = 'selected="selected"';
        }

        $systemColor = $row->getSystemColor();
        if ($systemColor !== null) {
            $options['option_' . $this->getSystemColorRenderer()
                ->calcOptionHash($systemColor)] = 'selected="selected"';
        }

        $alternateSystemColor = $row->getSystemColorAlternate();
        if ($alternateSystemColor !== null) {
            $options['option_' . $this->getAlternateSystemColorRenderer()
                ->calcOptionHash($alternateSystemColor)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getSystemCategoryRenderer()
    {
        if (!isset($this->systemCategoryRenderer)) {
            $this->systemCategoryRenderer = $this->getLayout()->createBlock(
                Category::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->systemCategoryRenderer;
    }

    private function getSystemTypeRenderer()
    {
        if (!isset($this->systemTypeRenderer)) {
            $this->systemTypeRenderer = $this->getLayout()->createBlock(
                Type::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->systemTypeRenderer;
    }

    private function getSystemColorRenderer()
    {
        if (!isset($this->systemColorRenderer)) {
            $this->systemColorRenderer = $this->getLayout()->createBlock(
                Color::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->systemColorRenderer;
    }

    private function getAlternateSystemColorRenderer()
    {
        if (!isset($this->alternateSystemColorRenderer)) {
            $this->alternateSystemColorRenderer = $this->getLayout()->createBlock(
                AlternateColor::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->alternateSystemColorRenderer;
    }
}
