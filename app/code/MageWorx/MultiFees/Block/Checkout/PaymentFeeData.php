<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\Checkout;

use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;

class PaymentFeeData extends AbstractFeeData
{
    /**
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMultifees(): AbstractCollection
    {
        return $this->feeCollectionValidationManager->getPaymentFeeCollection(
            FeeCollectionManagerInterface::HIDDEN_MODE_EXCLUDE
        );
    }
}
