<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Controller;

use Magefan\AlternateHreflang\Model\WoomCmsTreeFactory;

/**
 * Class Cms Page Router
 */
class RouterCms implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $switchers;
    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    protected $_pageRepository;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magefan\AlternateHreflang\Model\Config
     */
    protected $config;

    /**
     * @var woomCmsTreeFactory
     */
    private $woomCmsTreeFactory;

    /**
     * RouterCms constructor.
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magefan\AlternateHreflang\Model\Switchers $switchers
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magefan\AlternateHreflang\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magefan\AlternateHreflang\Model\Switchers $switchers,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\UrlInterface $url,
        \Magefan\AlternateHreflang\Model\Config $config,
        WoomCmsTreeFactory $woomCmsTreeFactory = null
    ) {
        $this->actionFactory = $actionFactory;
        $this->switchers = $switchers;
        $this->_response = $response;
        $this->_storeManager = $storeManager;
        $this->_pageRepository = $pageRepository;
        $this->_url = $url;
        $this->config = $config;
        $this->woomCmsTreeFactory = $woomCmsTreeFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magefan\AlternateHreflang\Model\WoomCmsTreeFactory::class);
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $_identifier = trim($request->getPathInfo(), '/');
        $_identifier = urldecode($_identifier);

        $storeId = $this->_storeManager->getStore()->getId();

        $switchers = $this->switchers->getSwitchers($_identifier, \Magefan\AlternateHreflang\Model\Switchers::CMS);
        /* Fix for Woom_CmsTree */
        if (!count($switchers)) {
            $_identifiers = explode('/', $_identifier);
            if (count($_identifiers) > 1) {
                $switchers = $this->switchers->getSwitchers($_identifiers[count($_identifiers) - 1], \Magefan\AlternateHreflang\Model\Switchers::CMS);
            }
        }
        /* End Fix */

        $redirectId = null;
        foreach ($switchers as $switcher) {
            if (!empty($switcher['localization'])) {
                $localization = json_decode($switcher['localization'], true);
            } else {
                $localization = [];
            }
            if (!empty($localization[$storeId])) {
                $redirectId = $localization[$storeId];
            }
        }

        if ($redirectId) {
            try {
                $page = $this->_pageRepository->getById($redirectId);

                $woomCmsTree = $this->woomCmsTreeFactory->getTreeByPageId($redirectId);
                if ($woomCmsTree) {
                    $redirectUrl = $woomCmsTree->getUrl();
                } else {
                    $redirectUrl =  $this->_url->getBaseUrl() . $page->getIdentifier();
                }

                $this->_response->setRedirect($redirectUrl, 301);
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    \Magento\Framework\App\Action\Redirect::class,
                    ['request' => $request]
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return;
            }
        }
    }
}
