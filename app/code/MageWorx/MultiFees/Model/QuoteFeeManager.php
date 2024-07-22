<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Exception;
use Magento\Quote\Model\Quote;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Helper\Data;
use MageWorx\MultiFees\Api\QuoteFeeManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use MageWorx\MultiFees\Helper\Price as HelperPrice;
use MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Api\Data\FeeDataInterface;
use \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;

class QuoteFeeManager extends AbstractFeeManager implements QuoteFeeManagerInterface
{
    /**
     * @var CartFeeCollectionFactory
     */
    protected $cartFeeCollectionFactory;

    /**
     * @var PaymentFeeCollectionFactory
     */
    protected $paymentFeeCollectionFactory;

    /**
     * @var ShippingFeeCollectionFactory
     */
    protected $shippingFeeCollectionFactory;

    /**
     * QuoteFeeManager constructor.
     *
     * @param CartFeeCollectionFactory $cartFeeCollectionFactory
     * @param PaymentFeeCollectionFactory $paymentFeeCollectionFactory
     * @param ShippingFeeCollectionFactory $shippingFeeCollectionFactory
     * @param OptionCollectionFactory $feeOptionCollectionFactory
     * @param SuitableRepositoryByFeeId $suitableRepositoryByFeeId
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param CartRepositoryInterface $cartRepository
     * @param Data $helperData
     * @param HelperPrice $helperPrice
     */
    public function __construct(
        CartFeeCollectionFactory $cartFeeCollectionFactory,
        PaymentFeeCollectionFactory $paymentFeeCollectionFactory,
        ShippingFeeCollectionFactory $shippingFeeCollectionFactory,
        OptionCollectionFactory $feeOptionCollectionFactory,
        SuitableRepositoryByFeeId $suitableRepositoryByFeeId,
        \Magento\Framework\Filter\FilterManager $filterManager,
        CartRepositoryInterface $cartRepository,
        Data $helperData,
        HelperPrice $helperPrice
    ) {
        parent::__construct(
            $feeOptionCollectionFactory,
            $suitableRepositoryByFeeId,
            $filterManager,
            $cartRepository,
            $helperData,
            $helperPrice
        );

        $this->cartFeeCollectionFactory     = $cartFeeCollectionFactory;
        $this->paymentFeeCollectionFactory  = $paymentFeeCollectionFactory;
        $this->shippingFeeCollectionFactory = $shippingFeeCollectionFactory;
        $this->feeDetailsName               = 'mageworx_fee_details';
    }

    /**
     * Get collection of the available cart fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAvailableCartFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::CART_TYPE);
    }

    /**
     * Get only required cart fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequiredCartFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::CART_TYPE, true);
    }

    /**
     * Get collection of the available shipping fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAvailableShippingFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::SHIPPING_TYPE);
    }

    /**
     * Get only required shipping fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequiredShippingFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::SHIPPING_TYPE, true);
    }

    /**
     * Get collection of the available payment fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAvailablePaymentFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::PAYMENT_TYPE);
    }

    /**
     * Get only required payment fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequiredPaymentFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::PAYMENT_TYPE, true);
    }

    /**
     * @param int $cartId
     * @param FeeDataInterface $fees
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setCartFees(int $cartId, FeeDataInterface $fees): array
    {
        return $this->setFees($cartId, $fees->getData(), AbstractFee::CART_TYPE);
    }

    /**
     * @param int $cartId
     * @param FeeDataInterface $fees
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setShippingFees(int $cartId, FeeDataInterface $fees): array
    {
        return $this->setFees($cartId, $fees->getData(), AbstractFee::SHIPPING_TYPE);
    }

    /**
     * @param int $cartId
     * @param FeeDataInterface $fees
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setPaymentFees(int $cartId, FeeDataInterface $fees): array
    {
        return $this->setFees($cartId, $fees->getData(), AbstractFee::PAYMENT_TYPE);
    }

    /**
     * @param int $cartId
     * @param int $type
     * @param bool $required
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getFees(int $cartId, int $type, bool $required = false): array
    {
        /** @var Quote $quote */
        $quote         = $this->cartRepository->get($cartId);
        $feeCollection = $this->getFeeCollection($quote, $type, $required);
        $feeCollection = $this->validateFeeCollectionByAddress($feeCollection, $quote);

        return $this->prepareFeesArray($feeCollection);
    }

    /**
     * @param int $cartId
     * @param array $fees
     * @param int $type
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function setFees(int $cartId, array $fees, int $type): array
    {
        $preparedFees = $this->prepareFeeData($fees, $type);

        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $this->addFeesToQuote(
            $preparedFees,
            $quote,
            true,
            $type,
            $this->getAddressFromQuote($quote)->getId()
        );

        $itemsCount = $quote->getItemsCount();
        if ($itemsCount) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $this->cartRepository->save($quote);
        }

        return $this->getFeeDetailFromQuote($quote);
    }

    /**
     * @param array $fees
     * @param int $type
     * @return array
     * @throws InputException
     */
    protected function prepareFeeData(array $fees, int $type): array
    {
        $result = [];

        foreach ($fees as $feeData) {
            if (!isset($feeData['id'])) {
                throw new InputException(__('A required field "id" missed.'));
            }

            if (!isset($feeData['options'])) {
                throw new InputException(__('A required field "options" missed.'));
            }

            $options    = explode(',', $feeData['options']);
            $newOptions = [];

            foreach ($options as $option) {
                $newOptions[$option] = [];
            }

            $data = ['options' => $newOptions, 'type' => $type];

            if (!empty($feeData['message'])) {
                $data['message'] = $feeData['message'];
            }
            if (!empty($feeData['date'])) {
                $data['date'] = $feeData['date'];
            }

            $result[$feeData['id']] = $data;
        }

        return $result;
    }

    /**
     * @param int $type
     * @return AbstractCollection
     * @throws Exception
     */
    protected function getFeeCollectionByType(int $type): AbstractCollection
    {
        switch ($type) {
            case AbstractFee::CART_TYPE:
                $feeCollectionFactory = $this->cartFeeCollectionFactory;
                break;
            case AbstractFee::PAYMENT_TYPE:
                $feeCollectionFactory = $this->paymentFeeCollectionFactory;
                break;
            case AbstractFee::SHIPPING_TYPE:
                $feeCollectionFactory = $this->shippingFeeCollectionFactory;
                break;
            default:
                throw new Exception(__('Unknown fee type %1', $type));
        }

        /** @var AbstractCollection $feeCollection */
        $feeCollection = $feeCollectionFactory->create();

        return $feeCollection;
    }

    /**
     * @param AbstractCollection $feeCollection
     * @param Quote $quote
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateFeeCollectionByAddress(AbstractCollection $feeCollection, Quote $quote): AbstractCollection
    {
        $address = $this->helperData->getSalesAddress($quote);
        /**
         * @var \MageWorx\MultiFees\Model\AbstractFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->canProcessFee($address, $quote)) {
                $feeCollection->removeItemByKey($key);
                continue;
            }
        }

        return $feeCollection;
    }

    /**
     * @param array $fees
     * @param int $type
     * @return array
     */
    protected function removeCurrentFee(array $fees, $type): array
    {
        foreach ($fees as $feeId => $data) {
            if ($data['type'] == $type) {
                // We remove fee with the current type, because they will be replaced with data from $feesPost
                unset($fees[$feeId]);
            }
        }

        return $fees;
    }

    /**
     *
     * return all fees from the form with the current specified type, and other types that were already in the quote
     *
     * @param Quote $quote
     * @param array $feesQuoteData
     * @param array $feesPost
     * @return array
     * @throws RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function modifyFeesDetailsByPostData(Quote $quote, array $feesQuoteData, array $feesPost): array
    {
        $optionIds = [];
        $feeIds    = array_keys($feesPost);
        foreach ($feesPost as $feeId => $feePostData) {
            if (empty($feePostData['options'])) {
                unset($feesPost[$feeId]);
                continue; // @protection: fee from the form has no options
            }

            foreach ($feePostData['options'] as $optionId => $optionData) {
                if (!$optionId) {
                    unset($feesPost[$feeId]['options'][$optionId]);
                    continue; // @protection: empty option
                }

                $optionIds[] = $optionId;
            }
        }

        /** @var \MageWorx\MultiFees\Model\ResourceModel\Option\Collection $optionCollection */
        $optionCollection = $this->feeOptionCollectionFactory->create();
        $optionCollection->addFeeOptionFilter(
            $optionIds
        )->addFeeFilter(
            $feeIds
        )->addStoreLanguage(
            $quote->getStoreId(),
            true
        )->load();

        foreach ($feesPost as $feeId => $feePostData) {
            $feesQuoteData[$feeId] = $this->modifyFeeDetails($feeId, $feePostData, $optionCollection);
        }

        $feesQuoteData = $this->filterMultiFeesInQuote($quote, $feesQuoteData);

        return $feesQuoteData;
    }

    /**
     * Helper method which filter the multi-fees in the quote and removes unacceptable values like:
     * 1) multi-fees having no options
     * 2) multi-fees with not valid options
     * 3) unavailable multi-fees model
     *
     * @param Quote $quote
     * @param array $feesQuoteData
     * @param int $storeId
     * @return array
     * @throws RefactoringException
     */
    protected function filterMultiFeesInQuote(Quote $quote, array $feesQuoteData = [], $storeId = 0): array
    {
        foreach ($feesQuoteData as $feeId => $data) {
            if (empty($data)) {
                continue;
            }

            $feesQuoteData[$feeId] = $this->filterMultiFee($feeId, $data);
            if (!$feesQuoteData[$feeId]) {
                unset($feesQuoteData[$feeId]);
            }
        }

        return $feesQuoteData;
    }
}
