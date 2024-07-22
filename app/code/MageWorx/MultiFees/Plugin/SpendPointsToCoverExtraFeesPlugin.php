<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Plugin;

use MageWorx\RewardPoints\Model\Total\Quote\SpendPoints;

class SpendPointsToCoverExtraFeesPlugin
{
    /**
     * @param SpendPoints $subject
     * @param float|int $result
     * @param string $code
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return float|int
     */
    public function afterGetBaseCustomCurrencyAmountByCode(SpendPoints $subject, $result, $code, $total)
    {
        if ($code === AddExtraFeesToApplyPointsToPlugin::APPLY_FOR_VALUE_EXTRA_FEES) {
            $baseFeeAmount           = floatval($total->getBaseMageworxFeeAmount());
            $baseProductFeeAmount    = floatval($total->getBaseMageworxProductFeeAmount());
            $baseFeeTaxAmount        = floatval($total->getBaseMageworxFeeTaxAmount());
            $baseProductFeeTaxAmount = floatval($total->getBaseMageworxProductFeeTaxAmount());

            return $baseFeeAmount + $baseProductFeeAmount - $baseFeeTaxAmount - $baseProductFeeTaxAmount;
        }

        return $result;
    }

    /**
     * @param SpendPoints $subject
     * @param float|int $result
     * @param string $code
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return float|int
     */
    public function afterGetCustomCurrencyAmountByCode(SpendPoints $subject, $result, $code, $total)
    {
        if ($code === AddExtraFeesToApplyPointsToPlugin::APPLY_FOR_VALUE_EXTRA_FEES) {
            $feeAmount           = floatval($total->getMageworxFeeAmount());
            $productFeeAmount    = floatval($total->getMageworxProductFeeAmount());
            $feeTaxAmount        = floatval($total->getMageworxFeeTaxAmount());
            $productFeeTaxAmount = floatval($total->getMageworxProductFeeTaxAmount());

            return $feeAmount + $productFeeAmount - $feeTaxAmount - $productFeeTaxAmount;
        }

        return $result;
    }
}