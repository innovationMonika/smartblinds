<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Block\PayPal\Express;

use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;

/**
 * Class PaymentFees
 */
class PaymentFees extends AbstractFee
{
    /**
     * Get corresponding fee collection
     *
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMultifees(): AbstractCollection
    {
        return $this->feeCollectionValidationManager->getPaymentFeeCollection(
            FeeCollectionManagerInterface::HIDDEN_MODE_EXCLUDE
        );
    }
}