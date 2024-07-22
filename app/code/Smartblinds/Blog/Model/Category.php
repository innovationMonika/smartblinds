<?php declare(strict_types=1);

namespace Smartblinds\Blog\Model;

class Category extends \Magefan\Blog\Model\Category
{
    public function getTitle()
    {
        return __($this->getData('title'));
    }

    public function getMetaTitle()
    {
        return __($this->getData('meta_title'));
    }

    public function getMetaKeywords()
    {
        return __($this->getData('meta_keywords'));
    }

    public function getMetaDescription()
    {
        return __($this->getData('meta_description'));
    }
}
