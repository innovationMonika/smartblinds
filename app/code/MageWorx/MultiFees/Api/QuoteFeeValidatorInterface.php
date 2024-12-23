<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Api;

use Magento\Quote\Model\Quote;
use MageWorx\MultiFees\Model\AbstractFee;

interface QuoteFeeValidatorInterface
{
    /**
     * @param Quote $quote
     * @param AbstractFee $fee
     * @return array
     */
    public function validateItems(Quote $quote, AbstractFee $fee): array;
}

