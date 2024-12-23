<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

/**
 * Class Allow to create and receive Magefan Blog object
 * Use ObjectManager as Magefan Blog cannot be installed together with this extension,
 * so cannot use object factories in the constructor.
 */
class BlogFactory implements \Magefan\AlternateHreflang\Api\BlogFactoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * BlogFactory constructor.
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
            \Magefan\Blog\Model\Post::class
        );
    }

    /**
     * @return mixed
     */
    public function createPostCollection()
    {
        return $this->objectManager->create(
            \Magefan\Blog\Model\ResourceModel\Post\Collection::class
        );
    }

    /**
     * @return mixed
     */
    public function createCategory()
    {
        return $this->objectManager->create(
            \Magefan\Blog\Model\Category::class
        );
    }

    /**
     * @return mixed
     */
    public function createCategoryCollection()
    {
        return $this->objectManager->create(
            \Magefan\Blog\Model\ResourceModel\Category\Collection::class
        );
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->objectManager->get(
            \Magefan\Blog\Model\Url::class
        );
    }

    /**
     * @return mixed
     */
    public function getUrlResolver()
    {
        return $this->objectManager->get(
            \Magefan\Blog\Api\UrlResolverInterface::class
        );
    }

    /**
     * @return mixed
     */
    public function createAuthor()
    {
        return $this->objectManager->create(
            \Magefan\Blog\Api\AuthorInterface::class
        );
    }

    /**
     * @return mixed
     */
    public function createAuthorCollection()
    {
        return $this->objectManager->create(
            \Magefan\Blog\Api\AuthorCollectionInterface::class
        );
    }

    /**
     * @return mixed
     */
    public function createTag()
    {
        return $this->objectManager->create(
            \Magefan\Blog\Model\Tag::class
        );
    }

    /**
     * @return mixed
     */
    public function createTagCollection()
    {
        return $this->objectManager->create(
            \Magefan\Blog\Model\ResourceModel\Tag\Collection::class
        );
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->objectManager->get(
            \Magefan\Blog\Model\Config::class
        );
    }

    /**
     * @return string
     */
    public function getBlogType()
    {
        return '';
    }
}
