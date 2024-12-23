<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

/**
 * Class Allow to create and receive Magefan SecondBlog object
 * Use ObjectManager as Magefan SecondBlog cannot be installed together with this extension,
 * so cannot use object factories in the constructor.
 */
class SecondBlogFactory implements \Magefan\AlternateHreflang\Api\BlogFactoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * SecondBlogFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->objectManager = $objectmanager;
    }

    /**
     * @return mixed
     */
    public function createPost()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Model\Post::class
        );
    }

    /**
     * @return mixed
     */
    public function createPostCollection()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Model\ResourceModel\Post\Collection::class
        );
    }

    /**
     * @return mixed
     */
    public function createCategory()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Model\Category::class
        );
    }

    /**
     * @return mixed
     */
    public function createCategoryCollection()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Model\ResourceModel\Category\Collection::class
        );
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->objectManager->get(
            \Magefan\SecondBlog\Model\Url::class
        );
    }

    /**
     * @return mixed
     */
    public function getUrlResolver()
    {
        return $this->objectManager->get(
            \Magefan\SecondBlog\Api\UrlResolverInterface::class
        );
    }

    /**
     * @return mixed
     */
    public function createAuthor()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Api\AuthorInterface::class
        );
    }

    /**
     * @return mixed
     */
    public function createAuthorCollection()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Api\AuthorCollectionInterface::class
        );
    }

    /**
     * @return mixed
     */
    public function createTag()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Model\Tag::class
        );
    }

    /**
     * @return mixed
     */
    public function createTagCollection()
    {
        return $this->objectManager->create(
            \Magefan\SecondBlog\Model\ResourceModel\Tag\Collection::class
        );
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->objectManager->get(
            \Magefan\SecondBlog\Model\Config::class
        );
    }

    /**
     * @return string
     */
    public function getBlogType()
    {
        return 'second';
    }
}
