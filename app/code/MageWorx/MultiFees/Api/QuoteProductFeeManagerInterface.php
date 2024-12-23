<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Api;

use Magento\Quote\Model\Quote;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\Data\ProductFeeDataInterface;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Model\AbstractFeeManager;

interface QuoteProductFeeManagerInterface
{
    /**
     * Get collection of the available product fees for the quote by items
     *
     * return array [
     *     [
     *        'quoteItemId'  => quoteItemId,
     *        'feesDetails'  => feesArray
     *     ]
     *]
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getAvailableProductFees(int $cartId): array;

    /**
     * Get only required products fees for the quote by items
     *
     * return array [
     *     [
     *        'quoteItemId'  => quoteItemId,
     *        'feesDetails'  => feesArray
     *     ]
     *]
     *
     * @param int $cartId
     * @return mixed[]
     */
    public function getRequiredProductFees(int $cartId): array;

    /**
     * Set product fee
     *
     * @param int $cartId
     * @param \MageWorx\MultiFees\Api\Data\ProductFeeDataInterface $feeData
     * @return mixed[]
     */
    public function setProductFees(int $cartId, ProductFeeDataInterface $feeData): array;

    /**
     * @param Quote $quote
     * @return array
     */
    public function getFeeDetailFromQuote(Quote $quote): array;

    /**
     * @param Quote $quote
     * @param array $details
     *@return AbstractFeeManager
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
