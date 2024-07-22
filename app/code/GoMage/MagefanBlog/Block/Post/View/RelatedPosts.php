<?php

namespace GoMage\MagefanBlog\Block\Post\View;

use GoMage\MagefanBlog\Model\ResourceModel\RelatedLoader;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magefan\Blog\Model\Url;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;

class RelatedPosts extends \Magefan\Blog\Block\Post\View\RelatedPosts
{
    private RelatedLoader $relatedLoader;
    private Json $json;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FilterProvider $filterProvider,
        CollectionFactory $postCollectionFactory,
        Url $url,
        RelatedLoader $relatedLoader,
        Json $json,
        array $data = [],
        $config = null
    ) {
        $this->relatedLoader = $relatedLoader;
        $this->json = $json;
        parent::__construct(
            $context, $coreRegistry, $filterProvider,
            $postCollectionFactory, $url, $data, $config
        );
    }

    protected function _preparePostCollection()
    {
//        $this->_postCollection = $this->relatedLoader->loadCollection($this->getPost());
        $this->_postCollection = $this->getPost()->getRelatedPosts();
    }

    public function getPostsJson()
    {
        return $this->json->serialize(array_values(array_map(function ($post) {
            return [
                'url' => $this->escapeUrl($post->getPostUrl()),
                'title' => $this->escapeHtml($post->getTitle()),
                'categories' => $this->getCategories($post),
                'imageUrl' => $post->getFeaturedListImage() ?: $post->getFeaturedImage()
            ];
        }, $this->getPostCollection()->getItems())));
    }

    private function getCategories($post)
    {
        $categories = array_map(function ($category) {
            return [
                'url' => $this->escapeUrl($category->getCategoryUrl()),
                'title' => $this->escapeHtml($category->getTitle()),
                'isLast' => false
            ];
        }, $post->getParentCategories()->getItems());

        $categories[array_key_last($categories)]['isLast'] = true;

        return array_values($categories);
    }
}
