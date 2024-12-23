<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Block\Widget\Feed;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Model\Config\Source\PostSize as PostSize;
use Amasty\InstagramFeed\Model\Instagram\Provider as PostProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

class AbstractGrid extends Template
{
    public const TITLE = 'title';

    public const POST_SIZE = 'post_size';

    public const RELATION_LINK_BLOCK_TEMPLATE = 'Amasty_InstagramFeed::widget/feed/components/relationButton.phtml';

    /**
     * @var PostProvider
     */
    protected $postProvider;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $encoder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        PostProvider $postProvider,
        Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->postProvider = $postProvider;
        $this->productRepository = $productRepository;
        $this->encoder = $encoder;
        $this->messageManager = $messageManager;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE) ?: '';
    }

    /**
     * @return array
     */
    public function getPosts()
    {
        return $this->postProvider->getPosts($this->getParams());
    }

    /**
     * @return bool
     */
    public function isPopupEnabled()
    {
        return (bool)$this->getData('click');
    }

    /**
     * @return bool
     */
    public function isShowDescription()
    {
        return (bool)$this->getData('show_description');
    }

    /**
     * @param array $post
     * @return mixed|string
     */
    public function getImageWidth(array $post)
    {
        $src = '';
        $postSize = $this->getPostSize();
        if (isset($post['images'][$postSize]['width'])) {
            $src = $post['images'][$postSize]['width'];
        }

        return $src;
    }

    /**
     * @param array $post
     * @return mixed|string
     */
    public function getImageHeight(array $post)
    {
        $src = '';
        $postSize = $this->getPostSize();
        if (isset($post['images'][$postSize]['height'])) {
            $src = $post['images'][$postSize]['height'];
        }

        return $src;
    }

    /**
     * @param array $post
     * @param $key
     *
     * @return mixed
     */
    public function getPostData(array $post, $key)
    {
        return isset($post[$key]) ? $post[$key] : '';
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return [
            'sort' => $this->getData('sort'),
            'count' => $this->getPostLimit()
        ];
    }

    /**
     * @return int
     */
    protected function getPostLimit()
    {
        return (int)$this->getData('posts_limit');
    }

    /**
     * @return string
     */
    protected function getPostSize()
    {
        return $this->getData(self::POST_SIZE) ?: PostSize::THUMBNAIL;
    }

    /**
     * @return int
     */
    public function getPostSizeNumber()
    {
        switch ($this->getPostSize()) {
            case PostSize::LOW_RESOLUTION:
                $size = PostSize::LOW_RESOLUTION_SIZE;
                break;
            case PostSize::STANDARD_RESOLUTION:
                $size = PostSize::STANDARD_RESOLUTION_SIZE;
                break;
            case PostSize::THUMBNAIL:
            default:
                $size = PostSize::THUMBNAIL_SIZE;
                break;
        }

        return $size;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo = array_merge($keyInfo, $this->getData());

        return $keyInfo;
    }

    /**
     * @return string
     */
    public function getPopupUrl()
    {
        return $this->_urlBuilder->getUrl('aminstagramfeed/post/single');
    }

    /**
     * @param PostInterface $post
     * @return string
     * @throws LocalizedException
     */
    public function getRelationLinkHtml(PostInterface $post)
    {
        $result = '';

        if ($post->getProductId()) {
            $product = null;
            if ($post->getProduct()) {
                $product = $post->getProduct();
            } else {
                try {
                    $product = $this->productRepository->getById($post->getProductId());
                } catch (NoSuchEntityException $e) {
                    return $result;
                }
            }
            if ($product) {
                $result = $this->getLayout()->createBlock(Template::class)
                    ->setTemplate(self::RELATION_LINK_BLOCK_TEMPLATE)
                    ->setProduct($product)
                    ->toHtml();
            }
        }

        return $result;
    }
}
