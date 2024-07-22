<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use Magento\Quote\Model\Quote;
use MageWorx\MultiFees\Api\QuoteProductFeeValidatorInterface;
use MageWorx\MultiFees\Helper\Data;

class QuoteProductFeeValidator extends AbstractFeeValidator implements QuoteProductFeeValidatorInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Quote $quote
     * @param AbstractFee $fee
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateItems(Quote $quote, AbstractFee $fee): array
    {
        $validItems = [];

        if ($this->helperData->isProductPage()) {
            $this->helperData->setCurrentItem($this->helperData->getQuoteItemFromCurrentProduct());
        }

        if ($this->helperData->getCurrentItem()) {
            $product = $this->helperData->getCurrentItem();

            if ($fee->getActions()->validate($product)) {
                $validItems[] = $product;
            }
        } else {
            $validItems = parent::validateItems($quote, $fee);
        }

        return $validItems;
    }
}
