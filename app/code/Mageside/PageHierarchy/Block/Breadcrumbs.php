<?php


namespace Mageside\PageHierarchy\Block;


use Magento\Cms\Model\Page;
use Magento\Framework\View\Element\Template\Context;
use Mageside\PageHierarchy\Helper\Config;

class Breadcrumbs extends \Magento\Framework\View\Element\Template
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
     * @var Page
     */
    private $page;

    /**
     * Breadcrumbs constructor.
     * @param Context $context
     * @param \Mageside\PageHierarchy\Helper\PageHierarchy $helper
     * @param Page $page
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Mageside\PageHierarchy\Helper\PageHierarchy $helper,
        Page $page,
        Config $config,
        $data = []
    )
    {
        parent::__construct($context, $data);
        $this->context = $context;
        $this->helper = $helper;
        $this->config = $config;
        $this->page = $page;
    }

    protected function _prepareLayout()
    {
        if ($this->config->isBreadcrumbs()) {
            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbs) {
                $this->helper->addCrumbs($this->page->getId(),$breadcrumbs,$this->page->getId());
            }
        }
        return parent::_prepareLayout();
    }
}