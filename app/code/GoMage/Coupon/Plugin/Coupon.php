<?php

namespace GoMage\Coupon\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Coupon
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @return bool
     */
    protected function _isDisplayed()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Coupon $subject
     * @param $result
     * @return mixed|string
     */
    public function afterToHtml(\Magento\Checkout\Block\Cart\Coupon $subject, $result)
    {
        if(!$this->_isDisplayed()){
            return '';
        }
        return $result;
    }
}
