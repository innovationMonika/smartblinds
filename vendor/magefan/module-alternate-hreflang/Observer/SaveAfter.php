<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Observer;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magefan\AlternateHreflang\Api\AlternateHreflangRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magefan\AlternateHreflang\Model\AlternateHreflangFactory;
use Magefan\AlternateHreflang\Model\Config;

/**
 * Class Save Object After Observer
 */
class SaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var AlternateHreflangRepositoryInterface
     */
    private $alternateHreflangRepository;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var AlternateHreflangFactory
     */
    private $alternateHreflangFactory;
    /**
     * @var int
     */
    private $type;
    /**
     * @var array
     */
    protected $repository;

    /**
     * SaveAfter constructor.
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AlternateHreflangRepositoryInterface $alternateHreflangRepository
     * @param RequestInterface $request
     * @param AlternateHreflangFactory $alternateHreflangFactory
     * @param array $repository
     * @param null $type
     */
    public function __construct(
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AlternateHreflangRepositoryInterface $alternateHreflangRepository,
        RequestInterface $request,
        AlternateHreflangFactory $alternateHreflangFactory,
        array $repository,
        $type = null
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->alternateHreflangRepository = $alternateHreflangRepository;
        $this->request = $request;
        $this->alternateHreflangFactory = $alternateHreflangFactory;
        $this->type = $type;
        $this->repository = $repository['object'];
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        if (in_array($this->type, [Config::BLOG_POST_TYPE, Config::BLOG_CATEGORY_TYPE, Config::BLOG_TAG_TYPE, Config::BLOG_AUTHOR_TYPE,
                                   Config::SECONDBLOG_POST_TYPE, Config::SECONDBLOG_CATEGORY_TYPE, Config::SECONDBLOG_TAG_TYPE, Config::SECONDBLOG_AUTHOR_TYPE,
                                   Config::STATIC_PAGE_TYPE])) {
            $object = $observer->getData('data_object');
            $stores = $object->getStores() ?: $object->getStoreIds();
        }  elseif (Config::CATALOG_PRODUCT_TYPE == $this->type) {
            $object = $observer->getData('product');
            $stores = $object->getStoreIds();
        } elseif (Config::CATALOG_CATEGORY_TYPE == $this->type) {
            $object = $observer->getData('category');
            $stores = $object->getStoreIds();
        }
        $parentId = $object->getId();
        $urlKey = $object->getIdentifier() ?: $object->getUrlKey();

        $localization = $this->request->getPost('localization');
        if ($localization && is_array($localization)) {
            foreach ($localization as $k => $v) {
                $v = explode('.', $v)[0];
                $v = (int)$v;
                if ($v) {
                    $localization[$k] = $v;
                } else {
                    unset($localization[$k]);
                }
            }

            $switcher = $this->getSwitcher($parentId);

            if ($localization) {
                $switcher
                    ->setUrlKey($urlKey)
                    ->setType($this->type)
                    ->setParentId($parentId)
                    ->setLocalization($localization);
                $this->alternateHreflangRepository->save($switcher);

                foreach ($localization as $storeId => $childSwitcherId) {
                    try {
                        if (Config::CATALOG_CATEGORY_TYPE == $this->type) {
                            $childObject = $this->repository->get($childSwitcherId);
                        } else {
                            $childObject = $this->repository->getById($childSwitcherId);
                        }
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        continue;
                    }

                    $childSwitcher = $this->getSwitcher($childObject->getId());
                    if (!$childSwitcher->getId()) {
                        $childSwitcher
                            ->setUrlKey($childObject->getIdentifier() ?: $childObject->getUrlKey())
                            ->setType($this->type)
                            ->setParentId($childObject->getId())
                            ->setLocalization([]);
                    }

                    $localization = $childSwitcher->getLocalization();
                    foreach ($stores as $store) {
                        if (!$store) {
                            continue;
                        }
                        $localization[$store] = (int)$parentId;
                    }
                    if (count($localization)) {
                        $childSwitcher->setLocalization($localization);
                        $this->alternateHreflangRepository->save($childSwitcher);
                    }
                }
            } else {
                if (!$localization && $switcher->getId()) {
                    $this->alternateHreflangRepository->deleteById($switcher->getId());
                }
            }
        }
    }

    /**
     * @param $parentId
     * @return \Magefan\AlternateHreflang\Model\AlternateHreflang|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSwitcher($parentId)
    {
        $switcherId = null;
        $filter = $this->filterBuilder
            ->setField('parent_id')
            ->setValue($parentId)
            ->create();
        $filter2 = $this->filterBuilder
            ->setField('type')
            ->setValue($this->type)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter, $filter2])->create();
        $switcherId = null;

        $getList = $this->alternateHreflangRepository->getList($searchCriteria)->getItems();

        if (isset($getList[0]['id'])) {
            $switcherId = (int)$getList[0]['id'];
        }

        if ($switcherId) {
            $switcher = $this->alternateHreflangRepository->getById($switcherId);
        } else {
            $switcher = $this->alternateHreflangFactory->create();
        }

        return $switcher;
    }
}
