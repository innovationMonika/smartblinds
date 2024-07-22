<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Images extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('image', [
            'label' => __('Image')
        ]);
        $this->addColumn('position', [
            'label' => __('Position')
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
