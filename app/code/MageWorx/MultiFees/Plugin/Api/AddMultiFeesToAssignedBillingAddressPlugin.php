<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */
declare(strict_types=1);

namespace MageWorx\MultiFees\Plugin\Api;

use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class AddMultiFeesToAssignedBillingAddressPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(CartRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param BillingAddressManagementInterface $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeAssign(
        BillingAddressManagementInterface $subject,
        $cartId,
        AddressInterface $address
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote      = $this->quoteRepository->getActive($cartId);
        $oldAddress = $quote->getBillingAddress();

        if (!empty($oldAddress->getMageworxProductFeeDetails())) {
            $address->setMageworxProductFeeDetails($oldAddress->getMageworxProductFeeDetails());
        }
        if (!empty($oldAddress->getMageworxFeeDetails())) {
            $address->setMageworxFeeDetails($oldAddress->getMageworxFeeDetails());
        }

        return null;
    }
}
