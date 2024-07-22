<?php declare(strict_types=1);

namespace GoMage\MagefanBlog\Model\ResourceModel;

use GoMage\MagefanBlog\Model\Config;
use Magefan\Blog\Block\Post\PostList\AbstractList;
use Magefan\Blog\Model\ResourceModel\Post\Collection;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Store\Model\StoreManagerInterface;

class RelatedLoader
{
    private Config $config;
    private CollectionFactory $collectionFactory;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    public function loadCollection($post): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addActiveFilter();

        $categories = $post->getCategories();
        if ($this->config->isAutoRelatedPosts() && $categories) {
            $collection
//                ->addCategoryFilter($categories)
                ->addFieldToFilter('post_id', ['neq' => $post->getId()])
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->setOrder(AbstractList::POSTS_SORT_FIELD_BY_PUBLISH_TIME, SortOrder::SORT_DESC);
            return $collection;
        }

        $collection->getSelect()->order('rl.position ' . SortOrder::SORT_ASC);
        return $collection;
    }
}
