<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Checkout;

class AbstractFee extends \Magento\Framework\App\Action\Action
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \MageWorx\MultiFees\Helper\BillingAddressManager
     */
    protected $billingAddressManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->quoteRepository       = $quoteRepository;
        $this->billingAddressManager = $billingAddressManager;
        $this->storeManager          = $storeManager;
        $this->cart                  = $cart;
        $this->logger                = $logger;
        $this->serializer            = $serializer;
    }

    /**
     * Initialize fees
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $cartQuote  = $this->cart->getQuote();
        $itemsCount = $cartQuote->getItemsCount();

        if (!$itemsCount) {
            return $this->getResponse()->setBody(
                $this->serializer->serialize(
                    [
                        'result' => 'false',
                        'error'  => __('Empty Quote'),
                        'reload' => 1
                    ]
                )
            );
        }

        $feesPost = $this->getPreparedFeePostData();
        $type     = $this->getRequest()->getPost('type');

        if ($this->getRequest()->getPost('billingAddressData')) {
            $this->billingAddressManager->transferBillingAddressDataToTheAddressObject(
                $this->getRequest()->getPost('billingAddressData')
            );
        }

        try {
            $this->quoteFeeManager->addFeesToQuote(
                $feesPost,
                $cartQuote,
                true,
                $type,
                $this->quoteFeeManager->getAddressFromQuote($cartQuote)->getId()
            );
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $this->quoteRepository->save($cartQuote);
            }
        } catch (\Exception $e) {
            $this->logger->error($e);

            return $this->getResponse()->setBody(
                $this->serializer->serialize(
                    [
                        'result' => 'false',
                        'error'  => $e->getMessage(),
                        'reload' => 1
                    ]
                )
            );
        }

        return $this->getResponse()->setBody(
            $this->serializer->serialize(
                [
                    'result' => 'true',
                    'reload' => 0
                ]
            )
        );
    }

    /**
     * Prepare post data array: convert options to array where the key of each option was option id
     *
     * @return array
     */
    protected function getPreparedFeePostData()
    {
        $feesPost = $this->getRequest()->getPost('fee');
        if (empty($feesPost)) {
            return [];
        }

        return $this->prepareFeeData($feesPost);
    }
}
