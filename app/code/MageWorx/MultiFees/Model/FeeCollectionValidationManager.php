<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\FeeCollectionManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection;
use MageWorx\MultiFees\Helper\Data as HelperFee;

class FeeCollectionValidationManager
{
    const DISABLED = 0;
    const ENABLED  = 1;

    /**
     * @var int
     */
    protected $status = self::DISABLED;

    /**
     * @var CartFeeCollection[]
     */
    protected $validCartFeeCollections;

    /**
     * @var ShippingFeeCollection[]
     */
    protected $validShippingFeeCollections;

    /**
     * @var PaymentFeeCollection[]
     */
    protected $validPaymentFeeCollections;

    /**
     * @var FeeCollectionManagerInterface
     */
    protected $feeCollectionManager;

    /**
     * @var int
     */
    protected $hiddenMode = FeeCollectionManagerInterface::HIDDEN_MODE_EXCLUDE;

    /**
     * @var HelperFee
     */
    protected $helperFee;

    /**
     * FeeCollectionValidationManager constructor.
     *
     * @param FeeCollectionManagerInterface $feeCollectionManager
     * @param HelperFee $helperFee
     */
    public function __construct(FeeCollectionManagerInterface $feeCollectionManager, HelperFee $helperFee)
    {
        $this->feeCollectionManager = $feeCollectionManager;
        $this->helperFee            = $helperFee;
    }

    /**
     * @return bool
     */
    public function isEnabledValidation(): bool
    {
        return (bool)$this->status;
    }

    /**
     * @param Quote $quote
     * @throws LocalizedException
     */
    public function prepareCollections(Quote $quote)
    {
        $this->feeCollectionManager->setQuote($quote);

        $key = 'hidden' . $this->hiddenMode;

        $this->validCartFeeCollections[$key]     = $this->feeCollectionManager->getCartFeeCollection(
            false,
            false,
            $this->hiddenMode
        );
        $this->validPaymentFeeCollections[$key]  = $this->feeCollectionManager->getPaymentFeeCollection(
            false,
            false,
            $this->hiddenMode
        );
        $this->validShippingFeeCollections[$key] = $this->feeCollectionManager->getShippingFeeCollection(
            false,
            false,
            $this->hiddenMode
        );
    }

    /**
     * @param int $hiddenMode
     * @return CartFeeCollection
     * @throws LocalizedException
     */
    public function getCartFeeCollection(int $hiddenMode): CartFeeCollection
    {
        $key = 'hidden' . $hiddenMode;

        if (isset($this->validCartFeeCollections[$key])) {
            return $this->validCartFeeCollections[$key];
        }

        $this->process($hiddenMode);

        return $this->validCartFeeCollections[$key];
    }

    /**
     * @param int $hiddenMode
     * @return PaymentFeeCollection
     * @throws LocalizedException
     */
    public function getPaymentFeeCollection(int $hiddenMode): PaymentFeeCollection
    {
        $key = 'hidden' . $hiddenMode;

        if (isset($this->validPaymentFeeCollections[$key])) {
            return $this->validPaymentFeeCollections[$key];
        }

        $this->process($hiddenMode);

        return $this->validPaymentFeeCollections[$key];
    }

    /**
     * @param int $hiddenMode
     * @return ShippingFeeCollection
     * @throws LocalizedException
     */
    public function getShippingFeeCollection(int $hiddenMode): ShippingFeeCollection
    {
        $key = 'hidden' . $hiddenMode;

        if (isset($this->validShippingFeeCollections[$key])) {
            return $this->validShippingFeeCollections[$key];
        }

        $this->process($hiddenMode);

        return $this->validShippingFeeCollections[$key];
    }

    /**
     * @return void
     */
    public function clearCollections()
    {
        $this->validCartFeeCollections     = null;
        $this->validPaymentFeeCollections  = null;
        $this->validShippingFeeCollections = null;
    }

    /**
     * @param int $hiddenMode
     * @throws LocalizedException
     */
    protected function process(int $hiddenMode)
    {
        $this->setHiddenMode($hiddenMode);
        $this->enableValidation();
        // @see \MageWorx\MultiFees\Observer\ValidateCollectionsByAddressObserver
        $this->helperFee->getCurrentSession()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        // fix for case when observer didn't call
        $this->prepareCollections($this->helperFee->getCurrentSession()->setTotalsCollectedFlag(false)->getQuote());
        $this->disableValidation();
    }

    /**
     * @return void
     */
    protected function enableValidation()
    {
        $this->status = self::ENABLED;
    }

    /**
     * @return void
     */
    protected function disableValidation()
    {
        $this->status = self::DISABLED;
    }

    /**
     * @param int $hiddenMode
     * @return FeeCollectionValidationManager
     */
    protected function setHiddenMode(int $hiddenMode): FeeCollectionValidationManager
    {
        $this->hiddenMode = $hiddenMode;

        return $this;
    }
}
