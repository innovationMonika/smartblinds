<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Cart;

use MageWorx\MultiFees\Model\CartFee as FeeModel;

class MassDelete extends MassAction
{
    /**
     * @param FeeModel $fee
     * @return $this
     */
    protected function executeAction(FeeModel $fee)
    {
        $this->feeRepository->delete($fee);

        return $this;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        return __('An error occurred while deleting record(s).');
    }

    /**
     * @param int $collectionSize
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize)
    {
        return __('A total of %1 record(s) have been deleted.', $collectionSize);
    }
}
