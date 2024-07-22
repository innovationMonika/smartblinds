<?php

namespace Smartblinds\Cms\Model\Config\Source;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class CmsBlocks implements OptionSourceInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    protected BlockRepositoryInterface $blockRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * CmsBlocks constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->blockRepository = $blockRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsBlocks = $this->blockRepository->getList($searchCriteria)->getItems();

        $arrResult = [['value' => '', 'label' => __('--Please Select--')]];

        foreach ($cmsBlocks as $block) {
            $arrResult[] = ['value' => $block->getIdentifier(), 'label' => $block->getTitle()];
        }
        return $arrResult;
    }
}
