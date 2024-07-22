<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use Magento\Quote\Model\ResourceModel\Quote;

class FeeQuoteRecollectTotalsOnDemand
{
    /**
     * @var Quote
     */
    protected $quoteResourceModel;

    /**
     * @param Quote $quoteResourceModel
     */
    public function __construct(Quote $quoteResourceModel)
    {
        $this->quoteResourceModel = $quoteResourceModel;
    }

    /**
     * Set "trigger_recollect" flag for active quotes which the given fee is applied to.
     *
     * @param int $feeId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(int $feeId): void
    {
        $connection = $this->quoteResourceModel->getConnection();
        $connection
            ->update(
                $this->quoteResourceModel->getMainTable(),
                ['trigger_recollect' => 1],
                [
                    'is_active = ?' => 1,
                    'FIND_IN_SET(?, applied_mageworx_fee_ids)' => $feeId
                ]
            );
    }
}
