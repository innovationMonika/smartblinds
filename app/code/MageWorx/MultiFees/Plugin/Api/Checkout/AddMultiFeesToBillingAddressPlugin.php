<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See https://www.mageworx.com/terms-and-conditions for license details.
 */
declare(strict_types=1);

namespace MageWorx\MultiFees\Plugin\Api\Checkout;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class AddMultiFeesToBillingAddressPlugin
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
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if ($billingAddress) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote      = $this->quoteRepository->getActive($cartId);
            $oldAddress = $quote->getBillingAddress();

            if (!empty($oldAddress->getMageworxProductFeeDetails())) {
                $billingAddress->setMageworxProductFeeDetails($oldAddress->getMageworxProductFeeDetails());
            }
            if (!empty($oldAddress->getMageworxFeeDetails())) {
                $billingAddress->setMageworxFeeDetails($oldAddress->getMageworxFeeDetails());
            }
        }

        return null;
    }
}
