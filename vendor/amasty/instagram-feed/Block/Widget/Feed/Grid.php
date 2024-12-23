<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Block\Widget\Feed;

use Amasty\InstagramFeed\Api\Data\PostInterface;

/**
 * Class Grid
 *
 * Implements grid of posts
 */
class Grid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_InstagramFeed::widget/feed/content/grid.phtml';

    /**
     * @var PostInterface[]
     */
    protected $allPosts = [];

    /**
     * @var PostInterface[]
     */
    protected $ajaxPosts = [];

    /**
     * @return array
     */
    public function getPosts()
    {
        $allPosts = $this->getAjaxPosts();
        if (!$allPosts) {
            $allPosts = parent::getPosts();
        }
        $this->allPosts = $allPosts;
        $posts = $this->isLoadMoreEnabled() ? array_slice($allPosts, 0, $this->getPostsPerPage()) : $allPosts;

        return $posts;
    }

    /**
     * @return string
     */
    public function getAllPostsJson()
    {
        $postsData = [];
        foreach ($this->getAllPosts() as $post) {
            $postsData[] = $post->getData();
        }

        return $this->encoder->encode($postsData);
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        return $this->encoder->encode($this->getData());
    }

    /**
     * @return bool
     */
    public function isLoadMoreEnabled()
    {
        return (bool)$this->getData('enable_load_more')
            && $this->getAllPostsCount() > $this->getPostsPerPage();
    }

    /**
     * @return int
     */
    public function getPostsPerPage()
    {
        return (int) $this->getData('posts_per_page') ?: 1;
    }

    /**
     * @return PostInterface[]
     */
    public function getAllPosts()
    {
        return $this->allPosts;
    }

    /**
     * @return int
     */
    public function getAllPostsCount()
    {
        return count($this->allPosts);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('aminstagramfeed/post/ajax');
    }

    /**
     * @return PostInterface[]
     */
    public function getAjaxPosts()
    {
        return $this->ajaxPosts;
    }

    /**
     * @param PostInterface[] $ajaxPosts
     */
    public function setAjaxPosts($ajaxPosts)
    {
        $this->ajaxPosts = $ajaxPosts;
    }
}
