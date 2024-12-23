<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

declare(strict_types=1);

namespace Amasty\InstagramFeed\Model;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Magento\Framework\Model\AbstractModel;

class Post extends AbstractModel implements PostInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Post::class);
    }

    /**
     * @inheritdoc
     */
    public function getPostId()
    {
        return $this->_getData(Post::POST_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPostId($postId)
    {
        $this->setData(Post::POST_ID, $postId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIgId()
    {
        return $this->_getData(Post::IG_ID);
    }

    /**
     * @inheritdoc
     */
    public function setIgId($igId)
    {
        $this->setData(Post::IG_ID, $igId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCommentsCount()
    {
        return $this->_getData(Post::COMMENTS_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function setCommentsCount($commentsCount)
    {
        $this->setData(Post::COMMENTS_COUNT, $commentsCount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLikeCount()
    {
        return $this->_getData(Post::LIKE_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function setLikeCount($likeCount)
    {
        $this->setData(Post::LIKE_COUNT, $likeCount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMediaUrl()
    {
        return $this->_getData(Post::MEDIA_URL);
    }

    /**
     * @inheritdoc
     */
    public function setMediaUrl($mediaUrl)
    {
        $this->setData(Post::MEDIA_URL, $mediaUrl);

        return $this;
    }
    
    public function getMediaType(): string
    {
        return (string)$this->_getData(PostInterface::MEDIA_TYPE);
    }

    public function setMediaType(string $mediaType): void
    {
        $this->setData(PostInterface::MEDIA_TYPE, $mediaType);
    }

    /**
     * @inheritdoc
     */
    public function getPermalink()
    {
        return $this->_getData(Post::PERMALINK);
    }

    /**
     * @inheritdoc
     */
    public function setPermalink($permalink)
    {
        $this->setData(Post::PERMALINK, $permalink);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShortcode()
    {
        return $this->_getData(Post::SHORTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setShortcode($shortcode)
    {
        $this->setData(Post::SHORTCODE, $shortcode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCaption()
    {
        return $this->_getData(Post::CAPTION);
    }

    /**
     * @inheritdoc
     */
    public function setCaption($caption)
    {
        $this->setData(Post::CAPTION, $caption);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp()
    {
        return $this->_getData(Post::TIMESTAMP);
    }

    /**
     * @inheritdoc
     */
    public function setTimestamp($timestamp)
    {
        $this->setData(Post::TIMESTAMP, $timestamp);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(Post::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(Post::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(PostInterface::PRODUCT_ID);
    }
}
