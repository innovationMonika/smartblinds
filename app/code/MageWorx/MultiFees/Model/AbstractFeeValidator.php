<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote;

abstract class AbstractFeeValidator
{
    /**
     * @param Quote $quote
     * @param AbstractFee $fee
     * @return array
     */
    public function validateItems(Quote $quote, AbstractFee $fee): array
    {
        $items      = $quote->getAllItems();
        $validItems = [];
        foreach ($items as $item) {
            if (!$item->getParentItemId() && $item->getProductType() == Configurable::TYPE_CODE) {
                // Skip validation for parent quote item id
                continue;
            }

            if ($fee->getActions()->validate($item)) {
                if ($item->getParentItemId() && $quote->getItemById($item->getParentItemId())) {
                    $item = $quote->getItemById($item->getParentItemId());
                }
                $validItems[$item->getId()] = $item;
            }
        }

        return $validItems;
    }
}
