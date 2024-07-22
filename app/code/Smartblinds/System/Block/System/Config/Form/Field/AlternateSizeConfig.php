<?php declare(strict_types=1);

namespace Smartblinds\System\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\System\Block\System\Config\Form\Field\Column\AlternateSize;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Category;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Size;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Type;

class AlternateSizeConfig extends AbstractFieldArray
{
    private Category $systemCategoryRenderer;
    private Type $systemTypeRenderer;
    private Size $systemSizeRenderer;
    private AlternateSize $alternateSystemSizeRenderer;

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
            'label' => __('System Size'),
            'renderer' => $this->getSystemSizeRenderer()
        ]);
        $this->addColumn('option_alternate', [
            'label' => __('Alternate System Size'),
            'renderer' => $this->getAlternateSystemSizeRenderer()
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
            $options['option_' . $this->getSystemTypeRenderer()->calcOptionHash($systemType)] = 'selected="selected"';
        }

        $systemSize = $row->getSystemSize();
        if ($systemSize !== null) {
            $options['option_' . $this->getSystemSizeRenderer()->calcOptionHash($systemSize)] = 'selected="selected"';
        }

        $alternateSystemSize = $row->getSystemSizeAlternate();
        if ($alternateSystemSize !== null) {
            $options['option_' . $this->getAlternateSystemSizeRenderer()->calcOptionHash($alternateSystemSize)] = 'selected="selected"';
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

    private function getSystemSizeRenderer()
    {
        if (!isset($this->systemSizeRenderer)) {
            $this->systemSizeRenderer = $this->getLayout()->createBlock(
                Size::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->systemSizeRenderer;
    }

    private function getAlternateSystemSizeRenderer()
    {
        if (!isset($this->alternateSystemSizeRenderer)) {
            $this->alternateSystemSizeRenderer = $this->getLayout()->createBlock(
                AlternateSize::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->alternateSystemSizeRenderer;
    }
}
