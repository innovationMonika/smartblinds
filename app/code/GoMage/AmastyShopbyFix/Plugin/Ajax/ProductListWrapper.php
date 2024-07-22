<?php

namespace GoMage\AmastyShopbyFix\Plugin\Ajax;

use Magento\Framework\View\Element\Template;

class ProductListWrapper
{
    private $_excludeBlocks = [
        'listproduct.js',
        'sb.category.info',
        'sb.discount.main',
        'sb.discount.sidebar'
    ];
    /**
     * @param \Amasty\Shopby\Plugin\Ajax\ProductListWrapper $subject
     * @param \Closure $proceed
     * @param Template $originalSubject
     * @param $result
     * @return string
     */
    public function aroundAfterToHtml(
        \Amasty\Shopby\Plugin\Ajax\ProductListWrapper $subject,
        \Closure $proceed,
        Template $originalSubject,
        $result
    )
    {
        if (in_array($originalSubject->getNameInLayout(), $this->_excludeBlocks)) {
            return $result;
        }

        return $proceed($originalSubject, $result);
    }
}
