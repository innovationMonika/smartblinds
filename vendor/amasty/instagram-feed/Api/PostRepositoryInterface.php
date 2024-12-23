<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Api;

use Amasty\InstagramFeed\Api\Data\PostInterface;

/**
 * @api
 */
interface PostRepositoryInterface
{
    /**
     * Create empty post object
     *
     * @return PostInterface
     */
    public function getPostObject();

    /**
     * Save
     *
     * @param \Amasty\InstagramFeed\Api\Data\PostInterface $post
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function save(\Amasty\InstagramFeed\Api\Data\PostInterface $post);

    /**
     * Get by id
     *
     * @param int $postId
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($postId);

    /**
     * Delete
     *
     * @param \Amasty\InstagramFeed\Api\Data\PostInterface $post
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\InstagramFeed\Api\Data\PostInterface $post);

    /**
     * Delete by id
     *
     * @param int $postId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($postId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get single post object by link for product relation
     *
     * @param $permalink
     * @return PostInterface
     */
    public function getByPermalink($permalink);

    /**
     * @param int $storeId
     * @param string $sortField
     * @param int $limit
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface[]
     */
    public function getPosts(int $storeId, string $sortField, int $limit);

    /**
     * @param int $storeId
     * @return bool
     */
    public function isPostsExist(int $storeId);

    /**
     * @param PostInterface $post
     * @return bool
     */
    public function unlink(PostInterface $post);
}
