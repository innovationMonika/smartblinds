<?php

namespace Smartblinds\Catalog\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class Price extends \Magento\Catalog\Model\Product\Attribute\Backend\Price
{
    public function setScope($attribute)
    {
        if ($attribute->getAttributeCode() === 'smartblinds_price') {
            return $this;
        }

        if ($this->_helper->isPriceGlobal()) {
            $attribute->setIsGlobal(ScopedAttributeInterface::SCOPE_GLOBAL);
        } else {
            $attribute->setIsGlobal(ScopedAttributeInterface::SCOPE_WEBSITE);
        }

        return $this;
    }
}
