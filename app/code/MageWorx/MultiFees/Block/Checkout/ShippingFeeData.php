<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\Checkout;

use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;

class ShippingFeeData extends AbstractFeeData
{
    /**
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMultifees(): AbstractCollection
    {
        return $this->feeCollectionValidationManager->getShippingFeeCollection(
            FeeCollectionManagerInterface::HIDDEN_MODE_EXCLUDE
        );
    }
}
