<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

declare(strict_types=1);

namespace Amasty\InstagramFeed\Model\ResourceModel\Post;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Model\Post;
use Amasty\InstagramFeed\Model\ResourceModel\Post as ResourcePost;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Post::class, ResourcePost::class);
        $this->_setIdFieldName(PostInterface::POST_ID);
    }

    /**
     * @return AbstractCollection
     */
    protected function _beforeLoad()
    {
        $relationTable = $this->getResource()->getTable(PostInterface::PRODUCT_RELATION_TABLE);
        $this->getSelect()->joinLeft(
            ['relation_table' => $relationTable],
            'relation_table.ig_id = main_table.ig_id',
            [PostInterface::PRODUCT_ID]
        );

        return parent::_beforeLoad();
    }
}
