<?php declare(strict_types=1);

namespace GoMage\Samples\Api\Claim;

use GoMage\Samples\Api\Data\Claim\InfoInterface;
use GoMage\Samples\Api\Data\Claim\ResultInterface;

/**
 * @api
 */
interface PlaceOrderInterface
{
    /**
     * @param \GoMage\Samples\Api\Data\Claim\InfoInterface $info
     * @return \GoMage\Samples\Api\Data\Claim\ResultInterface
     */
    public function place(InfoInterface $info): ResultInterface;
}
