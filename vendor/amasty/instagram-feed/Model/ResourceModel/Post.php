<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

declare(strict_types=1);

namespace Amasty\InstagramFeed\Model\ResourceModel;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Post extends AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PostInterface::MAIN_TABLE, PostInterface::POST_ID);
    }

    public function replaceData(array $postData, ?int $storeId): void
    {
        try {
            $this->beginTransaction();

            if ($storeId === null) {
                $this->getConnection()->delete($this->getMainTable());
            } else {
                $this->getConnection()->delete(
                    $this->getMainTable(),
                    [sprintf('%s = ?', PostInterface::STORE_ID) => $storeId]
                );
            }

            $this->getConnection()->insertMultiple($this->getMainTable(), $postData);
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }

        $this->commit();
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return AbstractDb
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getData(PostInterface::PRODUCT_ID)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(
                    $this->getTable(PostInterface::PRODUCT_RELATION_TABLE),
                    [PostInterface::PRODUCT_ID]
                )
                ->where(PostInterface::IG_ID . ' = ?', $object->getData(PostInterface::IG_ID));
            $productId = $connection->fetchOne($select);
            if (!empty($productId)) {
                $object->setData(PostInterface::PRODUCT_ID, $productId);
                $object->setOrigData(PostInterface::PRODUCT_ID, $productId);
            }
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return AbstractDb
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $productId = $object->getData(PostInterface::PRODUCT_ID);
        $originProductId = $object->getOrigData(PostInterface::PRODUCT_ID);
        if ($productId != $originProductId) {
            $this->saveRelationProduct($object->getData(PostInterface::IG_ID), $productId);
        }

        return parent::_afterSave($object);
    }

    /**
     * @param int $postIgId
     * @param int $productId
     * @return $this
     */
    public function saveRelationProduct($postIgId, $productId = null)
    {
        $this->getConnection()->delete(
            $this->getTable(PostInterface::PRODUCT_RELATION_TABLE),
            $this->getConnection()->quoteInto(PostInterface::IG_ID . ' = ?', $postIgId)
        );
        if ($productId !== null) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(PostInterface::PRODUCT_RELATION_TABLE),
                [PostInterface::IG_ID => $postIgId, PostInterface::PRODUCT_ID => $productId],
                [PostInterface::PRODUCT_ID]
            );
        }
        return $this;
    }
}
