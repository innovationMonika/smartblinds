<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

/**
 * Class retrieve switchers by path and page type
 */
class Switchers
{
    /**
     * Blog Post
     */
    const POST = 1;
    /**
     * Blog Category
     */
    const CATEGORY = 2;


    /**
     * Cms Page
     */
    const CMS = 3;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magefan\AlternateHreflang\Api\AlternateHreflangRepositoryInterface
     */
    private $alternateHreflangRepository;

    /**
     * Switchers constructor.
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magefan\AlternateHreflang\Api\AlternateHreflangRepositoryInterface $alternateHreflangRepository
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magefan\AlternateHreflang\Api\AlternateHreflangRepositoryInterface $alternateHreflangRepository
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->alternateHreflangRepository = $alternateHreflangRepository;
    }
    /**
     * @param $pathInfo
     * @param $type
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSwitchers($pathInfo, $type)
    {
        if (is_numeric($pathInfo)) {
            $filter1 = $this->filterBuilder
                ->setField('parent_id')
                ->setValue($pathInfo)
                ->create();
        } else {
            $filter1 = $this->filterBuilder
                ->setField('url_key')
                ->setValue($pathInfo)
                ->create();
        }

        $filter2 = $this->filterBuilder
            ->setField('type')
            ->setValue($type)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter1, $filter2])->create();
        return $this->alternateHreflangRepository->getList($searchCriteria)->getItems();
    }
}
