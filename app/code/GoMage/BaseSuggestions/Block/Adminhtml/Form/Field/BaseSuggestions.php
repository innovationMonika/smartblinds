<?php declare(strict_types=1);

namespace GoMage\BaseSuggestions\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class BaseSuggestions extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('label', [
            'label' => __('Label')
        ]);
        $this->addColumn('url', [
            'label' => __('URL')
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
