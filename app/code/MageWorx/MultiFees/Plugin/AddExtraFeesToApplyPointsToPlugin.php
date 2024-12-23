<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Plugin;

class AddExtraFeesToApplyPointsToPlugin
{
    const APPLY_FOR_VALUE_EXTRA_FEES = 'extra_fees';

    /**
     * @param \MageWorx\RewardPoints\Model\Source\ApplyFor $subject
     * @param array $result
     * @return array
     */
    public function afterToOptionArray(\MageWorx\RewardPoints\Model\Source\ApplyFor $subject, $result)
    {
        $result[] = ['label' => __('Extra fees'), 'value' => self::APPLY_FOR_VALUE_EXTRA_FEES];

        return $result;
    }
}