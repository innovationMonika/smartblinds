<?php

namespace GoMage\Breadcrumbs\Plugin\Magento\Theme\Block\Html;

use \Magento\Theme\Block\Html\Breadcrumbs as MagentoBreadCrumbs;

class Breadcrumbs
{
    /**
     * @var bool
     */
    private $_fixed = false;

    /**
     * @param MagentoBreadCrumbs $subject
     * @param string $crumbName
     * @param array $crumbInfo
     * @return array
     */
    public function beforeAddCrumb(MagentoBreadCrumbs $subject, $crumbName, $crumbInfo)
    {
        if (($crumbName == 'cms_page' || $crumbName == 'home') && !$this->_fixed) {
            $crumbInfo['label'] = __('Home');
            $crumbInfo['link']  = '/';
            $this->_fixed = true;
        }

        return [$crumbName, $crumbInfo];
    }
}
