<?php

namespace GoMage\Catalog\Block\Category;

use Magento\Framework\Exception\LocalizedException;

class View extends \Magento\Catalog\Block\Category\View
{
    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $category = $this->getCurrentCategory();
        if ($category) {
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($category->getCategoryName());
            }
        }

        return $this;
    }
}
