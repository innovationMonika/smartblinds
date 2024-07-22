<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\Data\ProductFeeDataInterface;

class ProductFeeData extends FeeData implements ProductFeeDataInterface
{
    /**
     * @var int|null
     */
    protected $itemId;

    /**
     * @return int|null
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     * @return ProductFeeDataInterface
     */
    public function setItemId(int $itemId): ProductFeeDataInterface
    {
        $this->itemId = $itemId;

        return $this;
    }
}
