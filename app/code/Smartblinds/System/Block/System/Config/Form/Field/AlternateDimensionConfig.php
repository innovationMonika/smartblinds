<?php declare(strict_types=1);

namespace Smartblinds\System\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Smartblinds\System\Block\System\Config\Form\Field\Column\Store;
use Smartblinds\System\Block\System\Config\Form\Field\Column\System;

class AlternateDimensionConfig extends AbstractFieldArray
{
    private Store $storeRenderer;
    private System $systemRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('store', [
            'label' => __('Store'),
            'renderer' => $this->getStoreRenderer()
        ]);
        $this->addColumn('system', [
            'label' => __('System'),
            'renderer' => $this->getSystemRenderer()
        ]);
        $this->addColumn('value', [
            'label' => __('Value')
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $systemCategory = $row->getStore();
        if ($systemCategory !== null) {
            $options['option_' . $this->getStoreRenderer()
                ->calcOptionHash($systemCategory)] = 'selected="selected"';
        }

        $systemType = $row->getSystem();
        if ($systemType !== null) {
            $options['option_' . $this->getSystemRenderer()->calcOptionHash($systemType)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getStoreRenderer()
    {
        if (!isset($this->storeRenderer)) {
            $this->storeRenderer = $this->getLayout()->createBlock(
                Store::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->storeRenderer;
    }

    private function getSystemRenderer()
    {
        if (!isset($this->systemRenderer)) {
            $this->systemRenderer = $this->getLayout()->createBlock(
                System::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->systemRenderer;
    }
}
