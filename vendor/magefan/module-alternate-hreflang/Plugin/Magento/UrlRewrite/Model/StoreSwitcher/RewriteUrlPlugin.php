<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Plugin\Magento\UrlRewrite\Model\StoreSwitcher;

use Magefan\AlternateHreflang\Model\Switchers;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\HTTP\PhpEnvironment\RequestFactory;
use Magento\UrlRewrite\Model\StoreSwitcher\RewriteUrl;
use Magefan\AlternateHreflang\Model\Config;
use Magefan\AlternateHreflang\Model\MagentoVersionsCms;

/**
 * Detect correct store switch redirect for CMS page
 */
class RewriteUrlPlugin
{
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var Switchers
     */
    protected $switchers;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MagentoVersionsCms
     */
    private $magentoVersionsCms;

    /**
     * RewriteUrlPlugin constructor.
     * @param RequestFactory $requestFactory
     * @param Switchers $switchers
     * @param PageRepositoryInterface $pageRepository
     * @param Config $config
     * @param MagentoVersionsCms $magentoVersionsCms
     */
    public function __construct(
        RequestFactory $requestFactory,
        Switchers $switchers,
        PageRepositoryInterface $pageRepository,
        Config $config,
        MagentoVersionsCms $magentoVersionsCms
    ) {
        $this->requestFactory = $requestFactory;
        $this->switchers = $switchers;
        $this->pageRepository = $pageRepository;
        $this->config = $config;
        $this->magentoVersionsCms = $magentoVersionsCms;
    }

    /**
     * @param RewriteUrl $subject
     * @param $result
     * @param $fromStore
     * @param $targetStore
     * @param $redirectUrl
     * @return string
     */
    public function afterSwitch(RewriteUrl $subject, $result, $fromStore, $targetStore, $redirectUrl)
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        $storeId = $targetStore->getId();
        $nodeCollection = $this->magentoVersionsCms->getNodeCollection($storeId);
        if ($nodeCollection) {
            $targetUrl = $redirectUrl;
            $request = $this->requestFactory->create(['uri' => $targetUrl]);
            $identifier = ltrim($request->getPathInfo(), '/');
            $identifier = explode('/', $identifier);
            $identifier = end($identifier);

            $redirectId = $this->getRedirectId($identifier, $storeId);
            if ($redirectId) {
                $node = $nodeCollection
                    ->addFieldToFilter('main_table.page_id', $redirectId)
                    ->getFirstItem();

                if (!$node->getId()) {
                    try {
                        $page = $this->pageRepository->getById($redirectId);
                        $result = $targetStore->getBaseUrl() . $page->getIdentifier();
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        $error = $e;
                    }
                } else {
                    $result = $node->getUrl($storeId);
                }
            }
        } elseif ($result != $redirectUrl && $result == $targetStore->getBaseUrl()) {
            $targetUrl = $redirectUrl;
            $request = $this->requestFactory->create(['uri' => $targetUrl]);
            $identifier = ltrim($request->getPathInfo(), '/');

            $storePath = $targetStore->getCode() . '/';
            if (0 === strpos($identifier,  $storePath)) {
                $identifier = substr($identifier, strlen($storePath));
            }

            $redirectId = $this->getRedirectId($identifier, $storeId);
            if ($redirectId) {
                try {
                    $page = $this->pageRepository->getById($redirectId);
                    $result = $targetStore->getBaseUrl() . $page->getIdentifier();
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $error = $e;
                }
            }
        }

        return $result;
    }

    /**
     * @param $identifier
     * @param $storeId
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRedirectId($identifier, $storeId)
    {
        $switchers = $this->switchers->getSwitchers($identifier, Switchers::CMS);
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

        return $redirectId;
    }
}
