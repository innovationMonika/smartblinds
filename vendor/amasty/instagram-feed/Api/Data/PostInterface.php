<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Api\Data;

interface PostInterface
{
    public const MAIN_TABLE = 'amasty_instagramfeed_post';
    public const PRODUCT_RELATION_TABLE = 'amasty_instagramfeed_post_product';

    public const POST_ID = 'post_id';
    public const IG_ID = 'ig_id';
    public const COMMENTS_COUNT = 'comments_count';
    public const LIKE_COUNT = 'like_count';
    public const MEDIA_URL = 'media_url';
    public const MEDIA_TYPE = 'media_type';
    public const PERMALINK = 'permalink';
    public const SHORTCODE = 'shortcode';
    public const CAPTION = 'caption';
    public const TIMESTAMP = 'timestamp';
    public const STORE_ID = 'store_id';
    public const PRODUCT_ID = 'product_id';

    /**
     * @return int
     */
    public function getPostId();

    /**
     * @param int $postId
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setPostId($postId);

    /**
     * @return string
     */
    public function getIgId();

    /**
     * @param string $igId
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setIgId($igId);

    /**
     * @return int
     */
    public function getCommentsCount();

    /**
     * @param int $commentsCount
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setCommentsCount($commentsCount);

    /**
     * @return int
     */
    public function getLikeCount();

    /**
     * @param int $likeCount
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setLikeCount($likeCount);

    /**
     * @return string
     */
    public function getMediaUrl();

    /**
     * @param string $mediaUrl
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setMediaUrl($mediaUrl);

    /**
     * @return string
     */
    public function getMediaType(): string;

    /**
     * @param string $mediaType
     *
     * @return void
     */
    public function setMediaType(string $mediaType): void;

    /**
     * @return string
     */
    public function getPermalink();

    /**
     * @param string $permalink
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setPermalink($permalink);

    /**
     * @return string
     */
    public function getShortcode();

    /**
     * @param string $shortcode
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setShortcode($shortcode);

    /**
     * @return string
     */
    public function getCaption();

    /**
     * @param string $caption
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setCaption($caption);

    /**
     * @return string
     */
    public function getTimestamp();

    /**
     * @param string $timestamp
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setTimestamp($timestamp);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\InstagramFeed\Api\Data\PostInterface
     */
    public function setStoreId($storeId);
}
