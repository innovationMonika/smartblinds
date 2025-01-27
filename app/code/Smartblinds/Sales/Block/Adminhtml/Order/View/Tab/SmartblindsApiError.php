<?php

namespace Smartblinds\Sales\Block\Adminhtml\Order\View\Tab;

class SmartblindsApiError extends \Magento\Backend\Block\Template implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'order/view/tab/smartblinds_api_error.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Smartblinds API');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Smartblinds API');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        // For me, I wanted this tab to always show
        // You can play around with the ACL settings
        // to selectively show later if you want
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        // For me, I wanted this tab to always show
        // You can play around with conditions to
        // show the tab later
        if (!$this->getOrder()->getSmartblindsRegistrationError()) {
            return true;
        }

        return false;
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        // I wanted mine to load via AJAX when it's selected
        // That's what this does
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl(
            'smartblinds_sales/order/apiTab', ['_current' => true, 'order_id' => $this->getOrder()->getId()]);
    }

    public function getBeatyErrror()
    {
        $error = $this->getOrder()->getSmartblindsRegistrationError();
        if (!empty($error)) {
            return $this->escapeHtml($error);
        }

       return '';
    }
}
