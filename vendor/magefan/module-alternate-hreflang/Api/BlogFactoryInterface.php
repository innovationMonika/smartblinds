<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Api;

interface BlogFactoryInterface
{

    /**
     * @return mixed
     */
    public function createPost();

    /**
     * @return mixed
     */
    public function createPostCollection();

    /**
     * @return mixed
     */
    public function createCategory();

    /**
     * @return mixed
     */
    public function createCategoryCollection();

    /**
     * @return mixed
     */
    public function getUrl();

    /**
     * @return mixed
     */
    public function getUrlResolver();

    /**
     * @return mixed
     */
    public function createAuthor();

    /**
     * @return mixed
     */
    public function createAuthorCollection();

    /**
     * @return mixed
     */
    public function createTag();

    /**
     * @return mixed
     */
    public function createTagCollection();

    /**
     * @return mixed
     */
    public function getConfig();

    /**
     * @return string
     */
    public function getBlogType();

}