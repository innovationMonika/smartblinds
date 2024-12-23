<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model;

/**
 * Class Alternate Hreflang Model
 * Keeps alt hreflang data
 */
class AlternateHreflang extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magefan_alternate_hreflang';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magefan\AlternateHreflang\Model\ResourceModel\AlternateHreflang::class);
    }
    /**
     * Retrieve id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData('id');
    }
    /**
     * Retrieve  url key
     *
     * @return int
     */
    public function getUrlKey()
    {
        return $this->getData('url_key');
    }
    /**
     * Retrieve type
     *
     * @return int
     */
    public function getType()
    {
        return $this->getData('type');
    }
    /**
     * Retrieve parent id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData('parent_id');
    }
    /**
     * Retrieve localization
     *
     * @return int
     */
    public function getLocalization()
    {
        return $this->getData('localization');
    }
    /**
     * Set ID
     *
     * @param int $id
     * @return mixed
     */
    public function setId($id)
    {
        return $this->setData('id', $id);
    }
    /**
     * Set url key
     *
     * @param string $urlKey
     * @return mixed
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData('url_key', $urlKey);
    }
    /**
     * Set type
     *
     * @param int $type
     * @return mixed
     */
    public function setType($type)
    {
        return $this->setData('type', $type);
    }
    /**
     * Set parent id
     *
     * @param int $parentId
     * @return mixed
     */
    public function setParentId($parentId)
    {
        return $this->setData('parent_id', $parentId);
    }
    /**
     * Set localization
     *
     * @param string $localization
     * @return mixed
     */
    public function setLocalization($localization)
    {
        return $this->setData('localization', $localization);
    }
}
