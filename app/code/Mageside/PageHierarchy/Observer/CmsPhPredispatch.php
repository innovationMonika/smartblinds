<?php

namespace Mageside\PageHierarchy\Observer;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageside\PageHierarchy\Helper\Config;
use Mageside\PageHierarchy\Helper\PageHierarchy;
use Mageside\PageHierarchy\Model\Config\DefaultRouteBehavior;

class CmsPhPredispatch implements ObserverInterface
{

    /**
     * @var PageHierarchy
     */
    private $helperPageHierarchy;
    /**
     * @var Config
     */
    private $phConfig;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        PageHierarchy $helperPageHierarchy,
        Config $phConfig,
        ForwardFactory $resultForwardFactory,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager
    )
    {
        $this->helperPageHierarchy = $helperPageHierarchy;
        $this->phConfig = $phConfig;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        if (!$this->phConfig->isEnabled()) {
            return null;
        }

        if (!$this->phConfig->isHierarchyPath()) {
            return null;
        }

        $request = $observer->getRequest();
        $identifier = trim($request->getPathInfo(), '/');
        $urls = array_reverse(explode('/', $identifier));
        if (empty($urls)) {
            return null;
        }

        $id = $urls[0];
        if (is_numeric($id)) {
            if ($this->helperPageHierarchy->hisParents($id)) {
                switch ($this->phConfig->getRouteBehavior()) {
                    case DefaultRouteBehavior::NOTFOUND:
                        $resultForward = $this->resultForwardFactory->create();
                        return $resultForward->forward('noroute');
                        break;
                    case DefaultRouteBehavior::PERMANENT_REDIRECT:
                        $controller = $observer->getEvent()->getControllerAction();
                        $url = $this->helperPageHierarchy->getHierarchyUrlById($id);
                        $controller->getResponse()->setRedirect($url, 301)->sendResponse();
                        break;
                }
            }
        }
    }
}
