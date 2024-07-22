<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Api;

use Magento\Quote\Model\Quote;
use MageWorx\MultiFees\Api\Data\FeeDataInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Model\AbstractFeeManager;

interface QuoteFeeManagerInterface
{
    /**
     * Get collection of the available cart fees for the quote
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getAvailableCartFees(int $cartId): array;

    /**
     * Get only required cart fees for the quote
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getRequiredCartFees(int $cartId): array;

    /**
     * Get collection of the available shipping fees for the quote
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getAvailableShippingFees(int $cartId): array;

    /**
     * Get only required shipping fees for the quote
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getRequiredShippingFees(int $cartId): array;

    /**
     * Get collection of the available payment fees for the quote
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getAvailablePaymentFees(int $cartId): array;

    /**
     * Get only required payment fees for the quote
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getRequiredPaymentFees(int $cartId): array;

    /**
     * Set cart fee
     *
     * @param int $cartId
     * @param \MageWorx\MultiFees\Api\Data\FeeDataInterface $feeData
     * @return mixed[]
     */
    public function setCartFees(int $cartId, FeeDataInterface $feeData): array;

    /**
     * Set shipping fee
     *
     * @param int $cartId
     * @param \MageWorx\MultiFees\Api\Data\FeeDataInterface $feeData
     * @return mixed[]
     */
    public function setShippingFees(int $cartId, FeeDataInterface $feeData): array;

    /**
     * Set payment fee
     *
     * @param int $cartId
     * @param \MageWorx\MultiFees\Api\Data\FeeDataInterface $feeData
     * @return mixed[]
     */
    public function setPaymentFees(int $cartId, FeeDataInterface $feeData): array;

    /**
     * @param Quote $quote
     * @return array
     */
    public function getFeeDetailFromQuote(Quote $quote): array;

    /**
     * @param Quote $quote
     * @param array $details
     * @return AbstractFeeManager
     */
    public function setFeeDetailToQuote(Quote $quote, array $details): AbstractFeeManager;

    /**
     * $addressId = 0 - default address
     *
     * @param Quote $quote
     * @param int $addressId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteDetailsMultifees(Quote $quote, $addressId = 0): array;

    /**
     * @param array $feesPost - data sent from the form
     * @param Quote $quote
     * @param bool $collect
     * @param int $type - the type of a fee from the form: it is important for filtering the fees to be replaced
     *                       with exactly this type of fees and not all of types
     * @param int $addressId
     * @return AbstractFeeManager
     * @throws RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFeesToQuote(
        array $feesPost,
        Quote $quote,
        $collect = true,
        $type = FeeInterface::CART_TYPE,
        $addressId = 0
    ): AbstractFeeManager;
}

