<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Api\Data;

/**
 * Interface ProductFeeDataInterface
 *
 * @api
 */
interface ProductFeeDataInterface extends FeeDataInterface
{
    /**
     * @param int $itemId
     * @return ProductFeeDataInterface
     */
    public function setItemId(int $itemId): ProductFeeDataInterface;

    /**
     * @return int|null
     */
    public function getItemId();
}
