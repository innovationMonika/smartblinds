<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Controller;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\OptionProvider;

/**
 * UrlRewrite Controller Router
 */
class RouterUrlRewrite extends \Magento\UrlRewrite\Controller\Router
{
    /**
     * @var \Magefan\AlternateHreflang\Model\Config
     */
    protected $config;

    /**
     * RouterUrlRewrite constructor.
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param \Magefan\AlternateHreflang\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magefan\AlternateHreflang\Model\Config $config
    ) {
        $this->config = $config;
        parent::__construct($actionFactory, $url, $storeManager, $response, $urlFinder);
    }

    /**
     * Match corresponding URL Rewrite and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|void|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        //If we're in the process of switching stores then matching rewrite
        //rule from previous store because the URL was not changed yet from
        //old store's format.
        foreach ($this->storeManager->getStores(true) as $store) {

            if (!$store->isActive()) {
                continue;
            }

            $oldStoreId = $store->getId();
            if ($oldStoreId == $this->storeManager->getStore()->getId()) {
                continue;
            }

            $oldRewrite = $this->getRewrite(
                $request->getPathInfo(),
                $oldStoreId
            );

            if ($oldRewrite && $oldRewrite->getRedirectType() === 0) {
                //If there is a match and it's a correct URL then just
                //redirecting to current store's URL equivalent,
                //otherwise just continuing finding a rule within current store.
                $currentRewrite = $this->urlFinder->findOneByData(
                    [
                        UrlRewrite::ENTITY_TYPE => $oldRewrite->getEntityType(),
                        UrlRewrite::ENTITY_ID => $oldRewrite->getEntityId(),
                        UrlRewrite::STORE_ID =>
                            $this->storeManager->getStore()->getId(),
                        UrlRewrite::REDIRECT_TYPE => 0,
                    ]
                );
                if ($currentRewrite
                    && $currentRewrite->getRequestPath()
                    !== $oldRewrite->getRequestPath()
                ) {
                    return $this->redirect(
                        $request,
                        $this->url->getUrl(
                            '',
                            ['_direct' => $currentRewrite->getRequestPath() . '?' . http_build_query($request->getParams())]
                        ),
                        OptionProvider::PERMANENT
                    );
                }
            }
        }
    }
}
