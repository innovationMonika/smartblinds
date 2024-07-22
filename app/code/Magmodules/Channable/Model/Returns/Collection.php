<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmodules\Channable\Model\Returns;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magmodules\Channable\Model\Returns\DataModel as ChannableReturnsData;
use Magmodules\Channable\Model\Returns\ResourceModel as ChannableReturnsResource;

/**
 * Returns collection
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            ChannableReturnsData::class,
            ChannableReturnsResource::class
        );
    }
}
