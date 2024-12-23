<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Api;

use Magefan\AlternateHreflang\Model\AlternateHreflang;

/**
 * Interface AlternateHreflangRepositoryInterface
 *
 * @api
 * @since 2.1.0
 */
interface AlternateHreflangRepositoryInterface
{
    /**
     * @param AlternateHreflang $alternateHreflang
     * @return mixed
     */
    public function save(AlternateHreflang $alternateHreflang);

    /**
     * @param $alternateHreflangId
     * @return mixed
     */
    public function getById($alternateHreflangId);

    /**
     * Retrieve AlternateHreflang matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param AlternateHreflang $alternateHreflang
     * @return mixed
     */
    public function delete(AlternateHreflang $alternateHreflang);

    /**
     * Delete AlternateHreflang by ID.
     *
     * @param int $alternateHreflangId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($alternateHreflangId);
}
