<?php declare(strict_types=1);

namespace Smartblinds\Cms\Block;

use Magento\Store\Model\ScopeInterface;

class Page extends \Magento\Cms\Block\Page
{
    protected function _prepareLayout()
    {
        $result = parent::_prepareLayout();

        $page = $this->getPage();
        $metaTitle = $page->getMetaTitle();
        $this->pageConfig->getTitle()->set(__($metaTitle ? $metaTitle : $page->getTitle()));
        $this->pageConfig->setKeywords(__($page->getMetaKeywords()));
        $this->pageConfig->setDescription(__($page->getMetaDescription()));

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->escapeHtml(__($pageMainTitle->getPageTitle())));
        }
        return $result;
    }

    protected function _addBreadcrumbs(\Magento\Cms\Model\Page $page)
    {
        $homePageIdentifier = $this->_scopeConfig->getValue(
            'web/default/cms_home_page',
            ScopeInterface::SCOPE_STORE
        );
        $homePageDelimiterPosition = strrpos($homePageIdentifier, '|');
        if ($homePageDelimiterPosition) {
            $homePageIdentifier = substr($homePageIdentifier, 0, $homePageDelimiterPosition);
        }
        $noRouteIdentifier = $this->_scopeConfig->getValue(
            'web/default/cms_no_route',
            ScopeInterface::SCOPE_STORE
        );
        $noRouteDelimiterPosition = strrpos($noRouteIdentifier, '|');
        if ($noRouteDelimiterPosition) {
            $noRouteIdentifier = substr($noRouteIdentifier, 0, $noRouteDelimiterPosition);
        }
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
            && $page->getIdentifier() !== $homePageIdentifier
            && $page->getIdentifier() !== $noRouteIdentifier
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb('cms_page', [
                'label' => __($page->getTitle()),
                'title' => __($page->getTitle())
            ]);
        }
    }
}
