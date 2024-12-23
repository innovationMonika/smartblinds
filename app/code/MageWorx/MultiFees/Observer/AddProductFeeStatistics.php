<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\MultiFees\Api\Data\CartFeeInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\Data\PaymentFeeInterface;
use MageWorx\MultiFees\Api\Data\ShippingFeeInterface;

/**
 * Class AddFeeStatistics
 *
 * @event sales_order_place_after
 */
class AddProductFeeStatistics implements ObserverInterface
{
    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * AddProductFeeStatistics constructor.
     *
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     */
    public function __construct(
        \MageWorx\MultiFees\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order    = $observer->getEvent()->getOrder();
        $feesData = $order->getMageworxProductFeeDetails();
        $feesData = $this->helperData->unserializeValue($feesData);

        foreach ($feesData as $feeId => $quoteItemData) {
            foreach ($quoteItemData as $quoteItemId => $data) {
                if (!isset($data['options'])) {
                    continue;
                }

                $repository = $this->helperData->getSuitableFeeRepositoryByType(
                    $data[FeeInterface::TYPE]
                );

                /** @var CartFeeInterface|ShippingFeeInterface|PaymentFeeInterface|FeeInterface $fee */
                $fee = $repository->getById($feeId);

                if ($fee->getId()) {
                    $fee->setTotalBaseAmount($fee->getTotalBaseAmount() + floatval($data['base_price']))
                        ->setTotalOrdered($fee->getTotalOrdered() + 1);
                    $repository->save($fee);
                }
            }
        }
        $this->clearSessionMageworxFeeDetails();

        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function clearSessionMageworxFeeDetails()
    {
        $this->helperData->getCurrentSession()->setMageworxProductFeeDetails(null);
    }
}
