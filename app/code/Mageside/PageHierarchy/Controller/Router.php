<?php
/**
 * Copyright © 2019 Mageside, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageside\PageHierarchy\Controller;

use Mageside\PageHierarchy\Helper\Config;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * @var \Mageside\PageHierarchy\Helper\PageHierarchy
     */
    private $helperPageHierarchy;

    /**
     * @var Config
     */
    private $phConfig;

    /**
     * Router constructor.
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Mageside\PageHierarchy\Helper\PageHierarchy $helperPageHierarchy
     * @param Config $phConfig
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Mageside\PageHierarchy\Helper\PageHierarchy $helperPageHierarchy,
        Config $phConfig

    )
    {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
        $this->helperPageHierarchy = $helperPageHierarchy;
        $this->phConfig = $phConfig;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if(!$this->phConfig->isEnabled()){
            return null;
        }

        if(!$this->phConfig->isHierarchyPath()){
            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);
        $this->_eventManager->dispatch(
            'ph_controller_router_match_before',
            ['router' => $this, 'condition' => $condition]
        );
        $identifier = $condition->getIdentifier();

        if ($condition->getRedirectUrl()) {
            $this->_response->setRedirect($condition->getRedirectUrl());
            $request->setDispatched(true);
            return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
        }

        if (!$condition->getContinue()) {
            return null;
        }

        $urls = array_reverse(explode('/', $identifier));
        if (empty($urls)) {
            return null;
        }

        $originUrl = $urls[0];

        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->_pageFactory->create();
        $pageId = $page->checkIdentifier($originUrl, $this->_storeManager->getStore()->getId());
        if (!$pageId) {
            return null;
        }

        while (count($urls) > 0) {
            $currentUrl = array_shift($urls);
            if(empty($urls)){
                break;
            }

            $parentUrl = $urls[0];
            $currentId = $page->checkIdentifier($currentUrl, $this->_storeManager->getStore()->getId());
            $prentId = $page->checkIdentifier($parentUrl, $this->_storeManager->getStore()->getId());
            if (!$this->helperPageHierarchy->hisParentById($currentId, $prentId)) {
                return null;
            }
        }

        $request->setModuleName('cms')->setControllerName('page')->setActionName('view')->setParam('page_id', $pageId);
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class,$request);
    }
}
