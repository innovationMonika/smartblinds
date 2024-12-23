<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\QuoteProductFeeManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Exception;
use Magento\Quote\Model\Quote;
use MageWorx\MultiFees\Api\Data\ProductFeeDataInterface;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Helper\Data;
use Magento\Quote\Api\CartRepositoryInterface;
use MageWorx\MultiFees\Helper\Price as HelperPrice;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;
use MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;

class QuoteProductFeeManager extends AbstractFeeManager implements QuoteProductFeeManagerInterface
{
    /**
     * @var ProductFeeCollectionFactory
     */
    protected $productFeeCollectionFactory;

    /**
     * @var int
     */
    protected $currentItemId;

    /**
     * QuoteProductFeeManager constructor.
     *
     * @param ProductFeeCollectionFactory $productFeeCollectionFactory
     * @param OptionCollectionFactory $feeOptionCollectionFactory
     * @param SuitableRepositoryByFeeId $suitableRepositoryByFeeId
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param CartRepositoryInterface $cartRepository
     * @param Data $helperData
     * @param HelperPrice $helperPrice
     */
    public function __construct(
        ProductFeeCollectionFactory $productFeeCollectionFactory,
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

        $this->productFeeCollectionFactory = $productFeeCollectionFactory;
        $this->feeDetailsName              = 'mageworx_product_fee_details';
    }

    /**
     * Get collection of the available product fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAvailableProductFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::PRODUCT_TYPE);
    }

    /**
     * Get only required products fees for the quote
     *
     * @param int $cartId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequiredProductFees(int $cartId): array
    {
        return $this->getFees($cartId, AbstractFee::PRODUCT_TYPE, true);
    }

    /**
     * @param int $cartId
     * @param ProductFeeDataInterface $fees
     * @return array|mixed
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setProductFees(int $cartId, ProductFeeDataInterface $fees): array
    {
        $quoteItemId = $fees->getItemId();

        if (!$quoteItemId) {
            throw new InputException(__('A required field "itemId" missed.'));
        }

        $this->currentItemId = $quoteItemId;

        $preparedFees = $this->prepareFeeData($fees->getData(), AbstractFee::PRODUCT_TYPE);

        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $this->addFeesToQuote(
            $preparedFees,
            $quote,
            true,
            AbstractFee::PRODUCT_TYPE,
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
        $fees          = [];
        $feeCollection = $this->getFeeCollection($quote, $type, $required);
        $feeArray      = $this->prepareFeesArray($feeCollection);

        foreach ($quote->getAllItems() as $item) {
            $fees[$item->getId()]['quoteItemId'] = $item->getId();

            $itemFees = $this->validateFeeCollectionByQuoteItem(
                $feeCollection,
                $item
            );

            $itemFeesArray = [];
            foreach (array_keys($itemFees->getItems()) as $id) {
                $itemFeesArray[] = $feeArray[$id];
            }

            $fees[$item->getId()]['feesDetails'] = $itemFeesArray;
        }

        return $fees;
    }

    /**
     * @param int $type
     * @return AbstractCollection
     * @throws Exception
     */
    protected function getFeeCollectionByType(int $type): AbstractCollection
    {
        switch ($type) {
            case AbstractFee::PRODUCT_TYPE:
                $feeCollectionFactory = $this->productFeeCollectionFactory;
                break;
            default:
                throw new Exception(__('Unknown product fee type %1', $type));
        }

        /** @var AbstractCollection $feeCollection */
        $feeCollection = $feeCollectionFactory->create();

        return $feeCollection;
    }

    /**
     * @param \MageWorx\MultiFees\Api\Data\FeeInterface[] $feeCollection
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return array|\MageWorx\MultiFees\Model\ResourceModel\Fee\ProductFeeCollection
     */
    public function validateFeeCollectionByQuoteItem(
        $feeCollection,
        $quoteItem
    ) {
        /**
         * @var \MageWorx\MultiFees\Model\ProductFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->getActions()->validate($quoteItem) || $quoteItem->getParentItemId()) {
                if (is_array($feeCollection)) {
                    unset($feeCollection[$key]);
                } else {
                    $feeCollection->removeItemByKey($key);
                }
            }
        }

        return $feeCollection;
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

            $result[$feeData['id']][$this->currentItemId] = $data;
        }

        return $result;
    }

    /**
     * @param array $feesPost
     * @param Quote $quote
     * @param bool $collect
     * @param int $type
     * @param int $addressId
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
        $preparedFeePost = [];

        foreach ($feesPost as $feeId => $data) {
            if (isset($data[$this->currentItemId])) {
                $preparedFeePost[$feeId] = [
                    $this->currentItemId => $data[$this->currentItemId]
                ];
            }
        }

        return parent::addFeesToQuote($feesPost, $quote, true, $type, $addressId);
    }

    /**
     * @param array $fees
     * @param int $type
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function removeCurrentFee(array $fees, $type): array
    {
        foreach ($fees as $feeId => $data) {
            if (isset($fees[$feeId][$this->currentItemId])) {
                if ($data[$this->currentItemId]['type'] == $type) {
                    // We remove fee with the current type, because they will be replaced with data from $feesPost
                    unset($fees[$feeId][$this->currentItemId]);
                }
            }
        }

        return $fees;
    }

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
    protected function modifyFeesDetailsByPostData(Quote $quote, array $feesQuoteData, array $feesPost): array
    {
        $optionIds = [];
        $feeIds    = array_keys($feesPost);
        foreach ($feesPost as $feeId => $data) {
            foreach ($data as $quoteItemId => $feePostData) {
                if (empty($feePostData['options'])) {
                    unset($feesPost[$feeId][$quoteItemId]);
                    continue; // @protection: fee from the form has no options
                }

                foreach ($feePostData['options'] as $optionId => $optionData) {
                    if (!$optionId) {
                        unset($feesPost[$feeId][$quoteItemId]['options'][$optionId]);
                        continue; // @protection: empty option
                    }

                    $optionIds[] = $optionId;
                }
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


        foreach ($feesPost as $feeId => $data) {
            foreach ($data as $quoteItemId => $feePostData) {
                $data = $this->modifyFeeDetails($feeId, $feePostData, $optionCollection);
                if (!$data) {
                    unset($feesQuoteData[$feeId][$quoteItemId]);
                } else {
                    $feesQuoteData[$feeId][$quoteItemId] = $data;
                }
            }
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
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    protected function filterMultiFeesInQuote(Quote $quote, array $feesQuoteData = []): array
    {
        $itemIds = $this->getValidQuoteItemIds($quote);
        foreach ($feesQuoteData as $feeId => $feesData) {
            foreach ($feesData as $itemId => $data) {
                if (array_search($itemId, $itemIds) === false) {
                    unset($feesQuoteData[$feeId][$itemId]);
                    continue;
                }

                $feesQuoteData[$feeId][$itemId] = $this->filterMultiFee($feeId, $data);
                if (!$feesQuoteData[$feeId][$itemId]) {
                    unset($feesQuoteData[$feeId][$itemId]);
                }
            }
        }

        return $feesQuoteData;
    }

    /**
     * @param Quote $quote
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValidQuoteItemIds(Quote $quote)
    {
        $validItems = [];
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $validItems[] = $item->getItemId();
            }
        }

        return $validItems;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setCurrentItemId(int $id): QuoteProductFeeManager
    {
        $this->currentItemId = $id;

        return $this;
    }
}
