<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Block\Widget\Feed;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Api\PostRepositoryInterface;
use Amasty\InstagramFeed\Model\Config\Source\MaxWidth;
use Amasty\InstagramFeed\Model\Instagram\Client;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

/**
 * Class Single
 *
 * Implements single post widget
 */
class Single extends Template
{
    public const RELATION_LINK_BLOCK_TEMPLATE = 'Amasty_InstagramFeed::widget/feed/components/relationButton.phtml';

    /**
     * @var string
     */
    protected $_template = 'Amasty_InstagramFeed::widget/feed/content/single.phtml';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var string
     */
    private $postUrl = '';

    /**
     * @var int
     */
    private $maxWidth = MaxWidth::MEDIUM;

    /**
     * @var bool
     */
    private $hideCaption = false;

    /**
     * @var PostInterface
     */
    private $post = null;

    public function __construct(
        Client $client,
        ProductRepositoryInterface $productRepository,
        PostRepositoryInterface $postRepository,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->client = $client;
        $this->productRepository = $productRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @return string
     */
    public function getCustomHtml()
    {
        return $this->client->loadSinglePostHtml(
            $this->getPostUrl(),
            $this->getMaxWidth(),
            (bool) $this->getHideCaption()
        );
    }

    /**
     * @return bool
     */
    public function getHideCaption()
    {
        return $this->getData('hide_caption') ?: $this->hideCaption;
    }

    /**
     * @param bool $hideCaption
     */
    public function setHideCaption($hideCaption)
    {
        $this->hideCaption = $hideCaption;
    }

    /**
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getData('post_url') ?: $this->postUrl;
    }

    /**
     * @param string $postUrl
     */
    public function setPostUrl($postUrl)
    {
        $this->postUrl = $postUrl;
    }

    /**
     * @return int
     */
    public function getMaxWidth()
    {
        return $this->getData('max_width') ?: $this->maxWidth;
    }

    /**
     * @param int $maxWidth
     */
    public function setMaxWidth($maxWidth)
    {
        $this->maxWidth = $maxWidth;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRelationLinkHtml()
    {
        $result = '';

        if ($this->getPost()->getProductId()) {
            $product = null;
            if ($this->getPost()->getProduct()) {
                $product = $this->getPost()->getProduct();
            } else {
                try {
                    $product = $this->productRepository->getById($this->getPost()->getProductId());
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

    /**
     * @return PostInterface
     */
    public function getPost()
    {
        if (!$this->post) {
            $this->post = $this->postRepository->getByPermalink($this->getPostUrl());
        }

        return $this->post;
    }
}
