<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\AlternateHreflang\Model;

use Magefan\AlternateHreflang\Api\AlternateHreflangRepositoryInterface;
use Magefan\AlternateHreflang\Model\AlternateHreflangFactory;
use Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang as AlternateHreflangResourceModel;
use Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang\CollectionFactory;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class Alternate Hreflang Model Repository
 */
class AlternateHreflangRepository implements AlternateHreflangRepositoryInterface
{
    /**
     * @var AlternateHreflangFactory
     */
    private $alternateHreflangFactory;
    /**
     * @var AlternateHreflangResourceModel
     */
    private $alternateHreflangResourceModel;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * AlternateHreflangRepository constructor.
     * @param AlternateHreflangFactory $alternateHreflangFactory
     * @param AlternateHreflangResourceModel $alternateHreflangResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     */
    public function __construct(
        AlternateHreflangFactory $alternateHreflangFactory,
        AlternateHreflangResourceModel $alternateHreflangResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory
    ) {
        $this->alternateHreflangFactory = $alternateHreflangFactory;
        $this->alternateHreflangResourceModel = $alternateHreflangResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param AlternateHreflang $alternateHreflang
     * @return bool|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(AlternateHreflang $alternateHreflang)
    {
        if ($alternateHreflang) {
            try {
                $this->alternateHreflangResourceModel->save($alternateHreflang);
            } catch (ConnectionException $exception) {
                throw new CouldNotSaveException(
                    __('Database connection error'),
                    $exception,
                    $exception->getCode()
                );
            } catch (CouldNotSaveException $e) {
                throw new CouldNotSaveException(__('Unable to save item'), $e);
            } catch (ValidatorException $e) {
                throw new CouldNotSaveException(__($e->getMessage()));
            }
            return $this->getById($alternateHreflang->getId());
        }
        return false;
    }

    /**
     * @param $alternateHreflangId
     * @param bool $editMode
     * @param null $storeId
     * @param bool $forceReload
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($alternateHreflangId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $alternateHreflang = $this->alternateHreflangFactory->create();
        $this->alternateHreflangResourceModel->load($alternateHreflang, $alternateHreflangId);
        if (!$alternateHreflang->getId()) {
            throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
        }
        return $alternateHreflang;
    }

    /**
     * @param AlternateHreflang $alternateHreflang
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function delete(AlternateHreflang $alternateHreflang)
    {
        try {
            $this->alternateHreflangResourceModel->delete($alternateHreflang);
        } catch (ValidatorException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove item')
            );
        }
        return true;
    }

    /**
     * @param int $alternateHreflangId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($alternateHreflangId)
    {
        return $this->delete($this->getById($alternateHreflangId));
    }
    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang\Collection $collection */
        $collection = $this->collectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        /** @var \Magento\Framework\Api\searchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getData());
        return $searchResult;
    }
}
