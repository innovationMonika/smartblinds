<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Controller;

use Magefan\AlternateHreflang\Api\BlogFactoryInterface as BlogFactory;
use Magento\Framework\Module\ModuleListInterface;
use Magefan\Blog\Model\Url;

/**
 * Class Blog Router
 */
class RouterBlog implements \Magento\Framework\App\RouterInterface
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
    protected $response;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $switchers;

    /**
     * @var array;
     */
    protected $ids;

    /**
     * @var BlogFactory
     */
    protected $blogFactory;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magefan\AlternateHreflang\Model\Config
     */
    protected $config;

    /**
     * @var mixed
     */
    protected $blogUrl;


    /**
     * RouterBlog constructor.
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magefan\AlternateHreflang\Model\Switchers $switchers
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param BlogFactory $blogFactory
     * @param ModuleListInterface $moduleList
     * @param \Magefan\AlternateHreflang\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magefan\AlternateHreflang\Model\Switchers $switchers,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        BlogFactory $blogFactory,
        ModuleListInterface $moduleList,
        \Magefan\AlternateHreflang\Model\Config $config
    ) {
        $this->actionFactory = $actionFactory;
        $this->switchers = $switchers;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->blogFactory = $blogFactory;
        $this->moduleList = $moduleList;
        $this->config = $config;
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function enabled($storeId)
    {
        return $this->config->isBlogPermalinkEnabled($storeId, $this->blogFactory->getBlogType())
             || $this->config->isBlogPlusPermalinkEnabled($storeId, $this->blogFactory->getBlogType());
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

        $storeId = $this->storeManager->getStore()->getId();

        $stores = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->isActive()) {
                continue;
            }
            $stores[] = $store->getId();
        }

        foreach ($stores as $_storeId) {
            if ($_storeId == $storeId) {
                continue;
            }

            if (!$this->enabled($storeId)) {
                continue;
            }

            $blogUrl = $this->getBlogUrl();

            $this->blogObjectStoreId = $_storeId;
            $_originStoreId = $blogUrl->getStoreId();
            $blogUrl->setStoreId($_storeId);
            $blogPage = $this->getBlogPage($_identifier);
            $blogUrl->setStoreId($_originStoreId);

            if (!$blogPage || empty($blogPage['type']) || empty($blogPage['id'])) {
                continue;
            }
            $redirectUrl = null;
            switch($blogPage['type']) {
                case Url::CONTROLLER_INDEX:
                    $redirectUrl = $blogUrl->getBaseUrl();
                    break;
                case Url::CONTROLLER_TAG:
                    $blogPage['type'] = 'tag';
                    $tag = $this->blogFactory->createTag()->load($blogPage['id']);
                    if ($tag->getId()) {
                        $redirectUrl = $tag->getTagUrl();
                    }
                    break;
                case Url::CONTROLLER_AUTHOR:
                    $blogPage['type'] = 'author';
                    $author = $this->blogFactory->createAuthor()->load($blogPage['id']);
                    if ($author->getId()) {
                        $redirectUrl = $author->getAuthorUrl();
                    }
                    break;
                /*
                case Url::CONTROLLER_RSS:
                    $redirectUrl = $blogUrl->getUrl(
                        $blogPage['id'],
                        $blogUrl::CONTROLLER_RSS
                    );
                    break;
                */
                case Url::CONTROLLER_SEARCH:
                    $redirectUrl = $blogUrl->getUrl(
                        $blogPage['id'],
                        $blogUrl::CONTROLLER_SEARCH
                    );
                    break;
                case Url::CONTROLLER_ARCHIVE:
                    $redirectUrl = $blogUrl->getUrl(
                        $blogPage['id'],
                        $blogUrl::CONTROLLER_ARCHIVE
                    );
                    break;
                case Url::CONTROLLER_POST:
                case Url::CONTROLLER_CATEGORY:
                    $switchers = $this->switchers->getSwitchers(
                        $blogPage['id'],
                        ($blogPage['type'] == Url::CONTROLLER_POST) ? \Magefan\AlternateHreflang\Model\Switchers::POST : \Magefan\AlternateHreflang\Model\Switchers::CATEGORY
                    );
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
                    if (!$redirectId) {
                        $redirectId = $blogPage['id'];
                    }

                    if ($blogPage['type'] == Url::CONTROLLER_POST) {
                        $post = $this->blogFactory->createPost()->load($redirectId);
                        if ($post->isVisibleOnStore($_originStoreId)) {
                            if ($post->isVisibleOnStore($storeId)) {
                                $redirectUrl = $post->getPostUrl();
                            } else {
                                $redirectUrl = $blogUrl->getBaseUrl();
                            }
                        }
                    } elseif ($blogPage['type'] == Url::CONTROLLER_CATEGORY) {
                        $category = $this->blogFactory->createCategory()->load($redirectId);
                        if ($category->isVisibleOnStore($_originStoreId)) {
                            if ($category->isVisibleOnStore($storeId)) {
                                $redirectUrl = $category->getCategoryUrl();
                            } else {
                                $redirectUrl = $blogUrl->getBaseUrl();
                            }
                        }
                    }
                    break;
            }

            if ($redirectUrl) {
                $this->response->setRedirect($redirectUrl, 301);
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    \Magento\Framework\App\Action\Redirect::class,
                    ['request' => $request]
                );
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getBlogUrl()
    {
        if (null === $this->blogUrl) {
            $this->blogUrl = $this->blogFactory->getUrl();
        }

        return $this->blogUrl;
    }

    /**
     * @param $_identifier
     * @return array|void|null
     */
    protected function getBlogPage($_identifier)
    {
        $urlResolver = $this->blogFactory->getUrlResolver();
        $urlResolver->setStoreId($this->getBlogUrl()->getStoreId());
        $blogPage = $urlResolver->resolve($_identifier);
        return $blogPage;
    }
}
