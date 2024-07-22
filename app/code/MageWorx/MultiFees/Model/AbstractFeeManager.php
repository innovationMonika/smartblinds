<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Helper\Data;
use Magento\Quote\Api\CartRepositoryInterface;
use MageWorx\MultiFees\Helper\Price as HelperPrice;
use MageWorx\MultiFees\Model\AbstractFee as FeeModel;
use MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;

abstract class AbstractFeeManager
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var OptionCollectionFactory
     */
    protected $feeOptionCollectionFactory;

    /**
     * @var SuitableRepositoryByFeeId
     */
    protected $suitableRepositoryByFeeId;

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var string
     */
    protected $feeDetailsName;

    /**
     * @var HelperPrice
     */
    protected $helperPrice;

    /**
     * AbstractFeeManager constructor.
     *
     * @param OptionCollectionFactory $feeOptionCollectionFactory
     * @param SuitableRepositoryByFeeId $suitableRepositoryByFeeId
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param CartRepositoryInterface $cartRepository
     * @param Data $helperData
     * @param HelperPrice $helperPrice
     */
    public function __construct(
        OptionCollectionFactory $feeOptionCollectionFactory,
        SuitableRepositoryByFeeId $suitableRepositoryByFeeId,
        \Magento\Framework\Filter\FilterManager $filterManager,
        CartRepositoryInterface $cartRepository,
        Data $helperData,
        HelperPrice $helperPrice
    ) {
        $this->feeOptionCollectionFactory = $feeOptionCollectionFactory;
        $this->suitableRepositoryByFeeId  = $suitableRepositoryByFeeId;
        $this->filterManager              = $filterManager;
        $this->cartRepository             = $cartRepository;
        $this->helperData                 = $helperData;
        $this->helperPrice                = $helperPrice;
    }

    /**
     * @param int $cartId
     * @param int $type
     * @param bool $required
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    abstract protected function getFees(int $cartId, int $type, bool $required = false): array;

    /**
     * @param array $fees
     * @param int $type
     * @return array
     * @throws InputException
     */
    abstract protected function prepareFeeData(array $fees, int $type): array;

    /**
     * @param array $fees
     * @param int $type
     * @return array
     */
    abstract protected function removeCurrentFee(array $fees, int $type): array;

    /**
     * Helper method which filter the multi-fees in the quote and removes unacceptable values like:
     * 1) multi-fees having no options
     * 2) multi-fees with not valid options
     * 3) unavailable multi-fees model
     *
     * @param Quote $quote
     * @param array $feesQuoteData
     * @return array
     * @throws RefactoringException
     */
    abstract protected function filterMultiFeesInQuote(Quote $quote, array $feesQuoteData = []): array;

    /**
     *
     * Return all fees from the form with the current specified type, and other types that were already in the quote
     *
     * @param Quote $quote
     * @param array $feesQuoteData
     * @param array $feesPost
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    abstract protected function modifyFeesDetailsByPostData(Quote $quote, array $feesQuoteData, array $feesPost): array;

    /**
     * @param int $type
     * @return AbstractCollection
     */
    abstract protected function getFeeCollectionByType(int $type): AbstractCollection;

    /**
     * @param Quote $quote
     * @param int $type
     * @param bool $required
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getFeeCollection(Quote $quote, int $type, bool $required = false): AbstractCollection
    {
        $feeCollection = $this->getFeeCollectionByType($type);

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $quote->getCustomerGroupId()
            )
            ->addRequiredFilter($required)
            ->addIsDefaultFilter(false)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        foreach ($feeCollection as $key => $fee) {
            $fee->setStoreId($quote->getStoreId());
        }

        return $feeCollection;
    }

    /**
     * @param Quote $quote
     * @return array
     */
    public function getFeeDetailFromQuote(Quote $quote): array
    {
        $address = $this->getAddressFromQuote($quote);
        $details = $address->getData($this->feeDetailsName);

        if (is_string($details)) {
            $details = $this->helperData->unserializeValue($details);
        }

        $result[(int)$address->getId()] = $details;

        return $result;
    }

    /**
     * @param Quote $quote
     * @param array $details
     * @return $this
     */
    public function setFeeDetailToQuote(Quote $quote, array $details): AbstractFeeManager
    {
        $address = $this->getAddressFromQuote($quote);
        $address->setData($this->feeDetailsName, $this->helperData->serializeValue($details));
        $address->save(); //@todo use repository

        return $this;
    }

    /**
     * @param Quote $quote
     * @return Address
     */
    public function getAddressFromQuote(Quote $quote): Address
    {
        if ($quote->isVirtual()) {
            return $quote->getBillingAddress();
        }

        return $quote->getShippingAddress();
    }

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
    ): AbstractFeeManager {
        $feesQuoteData = $this->getQuoteDetailsMultifees($quote, $addressId);
        $feesQuoteData = array_filter($feesQuoteData, 'is_array');
        $feesQuoteData = $this->removeCurrentFee($feesQuoteData, $type);
        $feesPost      = !empty($feesPost) ? $feesPost : [];
        $feesQuoteData = $this->modifyFeesDetailsByPostData($quote, $feesQuoteData, $feesPost);

        $feesQuoteData = array_filter($feesQuoteData, 'is_array');

        $this->setFeeDetailToQuote(
            $quote,
            $feesQuoteData
        ); // update the fees - add changes coming from the form

        if ($collect) {
            $quote->setTotalsCollectedFlag(false)->collectTotals();
        }

        return $this;
    }

    /**
     * $addressId = 0 - default address
     *
     * @param Quote $quote
     * @param int $addressId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteDetailsMultifees(Quote $quote, $addressId = 0): array
    {
        $feeDetailsData = $this->getFeeDetailFromQuote($quote);

        if (empty($feeDetailsData)) {
            return [];
        }

        $feeDetails = [];
        // get fees from default address
        if (isset($feeDetailsData[0])) {
            $feeDetails = $feeDetailsData[0];
        }

        // add fees from current address
        if ($addressId > 0 && !empty($feeDetailsData[$addressId])) {
            foreach ($feeDetailsData[$addressId] as $feeId => $feeData) {
                $feeDetails[$feeId] = $feeData;
            }
        }

        return $feeDetails;
    }

    /**
     * @param AbstractCollection $feeCollection
     * @return array
     */
    protected function prepareFeesArray(AbstractCollection $feeCollection): array
    {
        $feeArray = [];
        foreach ($feeCollection as $fee) {
            $feeArray[$fee->getId()] = $fee->getData();
            $options                 = [];
            foreach ($fee->getOptions() as $option) {
                $optionFormatPrice = $this->helperPrice->getOptionFormatPrice($option, $fee);
                $optionFieldLabel  = $option->getTitle() . '-' . $optionFormatPrice;
                $option->setData('field_label', $optionFieldLabel);
                $options[$option->getId()] = $option->getData();
            }
            $feeArray[$fee->getId()]['options'] = $options;
        }

        return $feeArray;
    }

    /**
     * @param int $feeId
     * @param array $feePostData
     * @param \MageWorx\MultiFees\Model\ResourceModel\Option\Collection $optionCollection
     * @return array|bool
     * @throws RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function modifyFeeDetails($feeId, $feePostData, $optionCollection)
    {
        $feesQuoteData = [];
        $repository    = $this->suitableRepositoryByFeeId->get($feeId);

        /** @var FeeModel $feeModel */
        $feeModel = $repository->getById($feeId);

        if (!is_object($feeModel) || (is_object($feeModel) && !$feeModel->getId())) {
            return $feesQuoteData; // @protection: fee does not exists
        }

        foreach ($feePostData['options'] as $optionId => $optionData) {
            $optionId = (int)$optionId;
            $opValue  = [];

            /** @var \MageWorx\MultiFees\Model\Option $option */
            $option = $optionCollection->getItemById($optionId);
            if (!$option || !$option->getId()) {
                continue;
            }
            $opValue['title'] = $option->getTitle();

            if ($option->getPriceType() == 'percent') {
                $opValue['percent'] = $option->getPrice();
            } else {
                $opValue['base_price'] = $option->getPrice();
            }
            $feesQuoteData['options'][$optionId] = $opValue;
        }

        if (isset($feesQuoteData['options'])) {
            $feesQuoteData['title']         = $feeModel->getTitle();
            $feesQuoteData['date_title']    = $feeModel->getDateFieldTitle();

            if (isset($feePostData['date'])) {
                $feesQuoteData['date'] = $this->filterDate($feePostData['date']);
            } else {
                $feesQuoteData['date'] = '';
            }

            $feesQuoteData['message_title'] = $feeModel->getCustomerMessageTitle();

            if (!empty($feePostData['message'])) {
                $feesQuoteData['message'] = $this->filterManager->truncate(
                    $feePostData['message'],
                    ['length' => 1024]
                );
            } else {
                $feesQuoteData['message'] = '';
            }

            $feesQuoteData['applied_totals'] = explode(',', $feeModel->getAppliedTotals());
        }
        if (!empty($feesQuoteData)) {
            $feesQuoteData['type']         = $feeModel->getType();
            $feesQuoteData['is_onetime']   = $feeModel->getIsOnetime();
            $feesQuoteData['tax_class_id'] = $feeModel->getTaxClassId();
        } else {
            return false;
        }

        return $feesQuoteData;
    }

    /**
     * @param string $date
     * @return string
     */
    protected function filterDate(string $date): string
    {
        $date = $this->filterManager->stringTrim($date);

        return $this->filterManager->stripTags($date);
    }

    /**
     * @param int $feeId
     * @param array $data
     * @return bool|array
     * @throws RefactoringException
     */
    protected function filterMultiFee($feeId, $data)
    {
        $repository = $this->suitableRepositoryByFeeId->get($feeId);
        /** @var FeeModel $feeModel */
        $feeModel = $repository->getById($feeId);

        // The fee filter
        if (!is_object($feeModel) || (is_object($feeModel) && !$feeModel->getId())) {
            return false; // @protection: fee does not exists
        }

        // The option filter
        foreach ($data['options'] as $optionId => $optionData) {
            $optionId = intval($optionId);
            if (!$optionId) {
                unset($data['options'][$optionId]);
                continue; // @protection: empty option
            }

            /** @var \MageWorx\MultiFees\Model\ResourceModel\Option\Collection $optionCollection */
            $optionCollection = $this->feeOptionCollectionFactory->create();
            $optionCollection->addFeeOptionFilter(
                $optionId
            )->addFeeFilter(
                $feeId
            )->load();
            /** @var \MageWorx\MultiFees\Model\Option $optionModel */
            $optionModel = $optionCollection->getFirstItem();
            if (!$optionModel->getId()) {
                unset($data['options'][$optionId]);
                continue;// @protection: empty option
            }
        }

        // Check for an empty options must be after the option filter
        if (empty($data['options'])) {
            return false; // @protection: fee from the form has no options
        }

        return $data;
    }
}
