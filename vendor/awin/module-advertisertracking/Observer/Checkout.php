<?php

namespace Awin\AdvertiserTracking\Observer;

class Checkout implements \Magento\Framework\Event\ObserverInterface
{
    protected $_cookieHandler;
    protected $_curl;
    protected $_scopeConfig;

    public function __construct(
        \Awin\AdvertiserTracking\Cookie\CookieHandler $cookieHandler,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_cookieHandler = $cookieHandler;
        $this->_curl = $curl;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if(isset($order))
        {
            $cks_from_cookie = $this->_cookieHandler->get("adv_awc");
            if ($cks_from_cookie && strlen($cks_from_cookie) == 0)
            {
                $cks_from_cookie = "";
            }
        
            $channel_from_cookie = $this->_cookieHandler->get("source");
            if($channel_from_cookie && strlen($channel_from_cookie) == 0)
            {
                $channel_from_cookie = "aw";
            }

            $url = $this->get_server_to_server_url($order, $cks_from_cookie, $channel_from_cookie);

            $this->_curl->get($url);
        }
    }

    public function get_server_to_server_url($order, $awc, $channel)
    {
        $advertiserId = $this->_scopeConfig->getValue('awin_settings/general/awin_advertiser_id', \Magento\Store\Model\ScopeInterface:: SCOPE_STORE);

        $orderId = $order->getRealOrderId();
        $grandTotal = $order->getGrandTotal();
        $taxTotal = $order->getTaxAmount();
        $shippingCost = $order->getShippingAmount();
        $totalAmount = $grandTotal - $shippingCost - $taxTotal;
        
        $couponCode = $order->getCouponCode();
        if(!$couponCode){
            $couponCode = $order->getDiscountDescription();
        }    
        $voucher = '';
        if($couponCode){
            $voucher = urlencode($couponCode);
        }
        
        $currency =  $order->getGlobalCurrencyCode();
        $p1 = 'magento2Module_1.2.1';

        return "https://awin1.com/sread.php?tt=ss&tv=2&cks={$awc}&merchant={$advertiserId}&amount={$totalAmount}&ch={$channel}&cr={$currency}&parts=DEFAULT:{$totalAmount}&ref={$orderId}&vc={$voucher}&p1={$p1}&testmode=0";
    }

}