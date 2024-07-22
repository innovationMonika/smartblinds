<?php /** @noinspection PhpCSValidationInspection */
/** @noinspection ALL */

/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Block;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;
use Mageside\PageHierarchy\Helper\Config;
use Mageside\PageHierarchy\Model\Config\TreeSourceOptionsSelect;

/**
 * Class HierarchyPage
 * @package Mageside\CMSPageHierarchy\Block
 */
class PageHierarchy extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var PageHierarchy
     */
    private $helper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Cms\Model\Page
     */
    private $page;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * PageHierarchy constructor.
     * @param Context $context
     * @param \Mageside\PageHierarchy\Helper\PageHierarchy $helper
     * @param \Magento\Cms\Model\Page $page
     * @param Config $config
     */
    public function __construct(
        Context $context,
        \Mageside\PageHierarchy\Helper\PageHierarchy $helper,
        \Magento\Cms\Model\Page $page,
        Config $config,
        $data = []
    )
    {
        parent::__construct($context, $data);
        $this->context = $context;
        $this->helper = $helper;
        $this->config = $config;
        $this->page = $page;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return string
     */
    public function printPageHierarchy()
    {
        $menu = "";
        if (!$this->page->getData('show_menu_hierarchy')) {
            return $menu;
        }

        $rootId = 0;
        switch ($this->config->treeSource()) {
            case TreeSourceOptionsSelect::SIBLINGS:
                $rootId = $rootId = $this->page->getData('parent_page_id');
                break;
            case TreeSourceOptionsSelect::CHILDREN:
                $rootId = $rootId = $this->page->getId();
                break;
        }

        $tree = $this->helper->getArrayHierarchy(
            \Mageside\PageHierarchy\Helper\PageHierarchy::FRONT,
            $this->config,
            $rootId,
            $this->getStoreId()
        );

        foreach ($tree as $items) {
            $this->printItems($items, $menu);
        }
        return $menu;
    }

    /**
     * @param $items
     * @param string $menu
     */
    protected function printItems($items, &$menu = "")
    {
        $menu .= "<ul class = 'hierarchy-links-list'>";
        foreach ($items as $item) {
            $childrenMenu = "";
            if (!empty($item['children'])) {
                $childrenMenu .= $this->printItems($item['children'], $childrenMenu);
            }

            $menu .= "<li>" .
                "<a class= 'cms-hierarchy-item " . $this->getPageHtmlClass($item) . "' " .
                "href=" . $this->urlBuilder->getUrl(null, ['_direct' => $item['identifier']]) . ">" .
                "<span>" . $item['title'] . "</span>" . "</a>" .
                $childrenMenu
                . "</li>";
        }
        $menu .= "</ul>";
    }

    /**
     * @param $item
     * @return mixed
     */
    protected function getPageHtmlClass($item)
    {
        return $item['page_id'] == $this->page->getId() ? "current-page" : "";
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->context->getStoreManager()->getStore()->getId();
    }
}
