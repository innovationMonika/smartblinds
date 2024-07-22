<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use MageWorx\MultiFees\Model\FeeCollectionValidationManager;

class ValidateCollectionsByAddressObserver implements ObserverInterface
{
    /**
     * @var FeeCollectionValidationManager
     */
    protected $feeCollectionValidationManager;

    /**
     * ValidateCollectionsByAddressObserver constructor.
     *
     * @param FeeCollectionValidationManager $feeCollectionValidationManager
     */
    public function __construct(FeeCollectionValidationManager $feeCollectionValidationManager)
    {
        $this->feeCollectionValidationManager = $feeCollectionValidationManager;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->feeCollectionValidationManager->isEnabledValidation()) {
            $event = $observer->getEvent();

            /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment */
            $shippingAssignment = $event->getShippingAssignment();

            if (count($shippingAssignment->getItems())) {
                $this->feeCollectionValidationManager->prepareCollections($event->getQuote());
            }
        }
    }
}
