<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Model\Repository;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Api\PostRepositoryInterface;
use Amasty\InstagramFeed\Model\PostFactory;
use Amasty\InstagramFeed\Model\ResourceModel\Post as PostResource;
use Amasty\InstagramFeed\Model\ResourceModel\Post\CollectionFactory;
use Amasty\InstagramFeed\Model\ResourceModel\Post\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * @var PostResource
     */
    private $postResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $posts;

    /**
     * @var CollectionFactory
     */
    private $postCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        PostFactory $postFactory,
        PostResource $postResource,
        CollectionFactory $postCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->postFactory = $postFactory;
        $this->postResource = $postResource;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * @return PostInterface
     */
    public function getPostObject()
    {
        return $this->postFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function save(PostInterface $post)
    {
        try {
            if ($post->getPostId()) {
                $post = $this->getById($post->getPostId())->addData($post->getData());
            }
            $this->postResource->save($post);
            unset($this->posts[$post->getPostId()]);
        } catch (\Exception $e) {
            if ($post->getPostId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save post with ID %1. Error: %2',
                        [$post->getPostId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new post. Error: %1', $e->getMessage()));
        }

        return $post;
    }

    /**
     * @inheritdoc
     */
    public function getById($postId)
    {
        if (!isset($this->posts[$postId])) {
            /** @var \Amasty\InstagramFeed\Model\Post $post */
            $post = $this->postFactory->create();
            $this->postResource->load($post, $postId);
            if (!$post->getPostId()) {
                throw new NoSuchEntityException(__('Post with specified ID "%1" not found.', $postId));
            }
            $this->posts[$postId] = $post;
        }

        return $this->posts[$postId];
    }

    /**
     * @inheritdoc
     */
    public function delete(PostInterface $post)
    {
        try {
            $this->postResource->delete($post);
            unset($this->posts[$post->getPostId()]);
        } catch (\Exception $e) {
            if ($post->getPostId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove post with ID %1. Error: %2',
                        [$post->getPostId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove post. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($postId)
    {
        $postModel = $this->getById($postId);
        $this->delete($postModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\InstagramFeed\Model\ResourceModel\Post\Collection $postCollection */
        $postCollection = $this->postCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $postCollection);
        }

        $searchResults->setTotalCount($postCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $postCollection);
        }

        $postCollection->setCurPage($searchCriteria->getCurrentPage());
        $postCollection->setPageSize($searchCriteria->getPageSize());

        $posts = [];
        /** @var PostInterface $post */
        foreach ($postCollection->getItems() as $post) {
            $posts[] = $this->getById($post->getPostId());
        }

        $searchResults->setItems($posts);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $postCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $postCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $postCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $postCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $postCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $postCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @param int $storeId
     * @param string $sortField
     * @param int $limit
     * @return PostInterface[]
     */
    public function getPosts(int $storeId, string $sortField, int $limit, int $page = 1)
    {
        /** @var \Amasty\InstagramFeed\Model\ResourceModel\Post\Collection $postCollection */
        $postCollection = $this->postCollectionFactory->create()
            ->addFieldToFilter(PostInterface::STORE_ID, $storeId)
            ->setOrder($sortField, Select::SQL_DESC)
            ->setCurPage($page)
            ->setPageSize($limit);

        $posts = [];
        /** @var PostInterface $post */
        foreach ($postCollection->getItems() as $post) {
            $posts[] = $post;
        }

        return $posts;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isPostsExist(int $storeId)
    {
        return (bool) $this->postCollectionFactory->create()
            ->addFieldToFilter(PostInterface::STORE_ID, $storeId)
            ->getSize();
    }

    /**
     * @param $permalink
     * @return PostInterface
     */
    public function getByPermalink($permalink)
    {
        $post = $this->getPostObject();
        $this->postResource->load($post, $permalink, PostInterface::PERMALINK);
        return $post;
    }

    /**
     * @inheritdoc
     */
    public function unlink(PostInterface $post)
    {
        $this->postResource->saveRelationProduct($post->getIgId(), null);
        return $this;
    }
}
