<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use MageWorx\MultiFees\Model\PaymentFeeFactory as FeeFactory;
use MageWorx\MultiFees\Api\PaymentFeeRepositoryInterface as FeeRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class PaymentFee extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageWorx_MultiFees::multifees';

    /**
     * Fee factory
     *
     * @var FeeFactory
     */
    protected $feeFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var FeeRepository
     */
    protected $feeRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * PaymentFee constructor.
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Registry $registry
     * @param FeeFactory $feeFactory
     * @param FeeRepository $feeRepository
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\SalesRule\Model\Rule $rule,
        Registry $registry,
        FeeFactory $feeFactory,
        FeeRepository $feeRepository,
        PageFactory $resultPageFactory,
        JsonFactory $jsonFactory,
        Context $context
    ) {
        $this->rule                  = $rule;
        $this->coreRegistry          = $registry;
        $this->feeFactory            = $feeFactory;
        $this->feeRepository         = $feeRepository;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultPageFactory     = $resultPageFactory;
        $this->jsonFactory           = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \MageWorx\MultiFees\Model\PaymentFee
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initFee()
    {
        $feeId = $this->getRequest()->getParam('fee_id');
        if ($feeId) {
            $fee = $this->feeRepository->getById($feeId);
        } else {
            $fee = $this->feeFactory->create();
        }
        $this->coreRegistry->register('mageworx_multifees_fee', $fee);

        return $fee;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    protected function initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageWorx_MultiFees::multifees_payment');
        $resultPage->getConfig()->getTitle()->prepend(__('Payment Fee'));
        $resultPage->addBreadcrumb(__('MultiFees'), __('MultiFees'));
        $resultPage->addBreadcrumb(__('Payment Fee'), __('Payment Fee'));

        return $resultPage;
    }

    /**
     * filter data
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        return $data;
    }
}
