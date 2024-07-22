<?php

namespace GoMage\Checkout\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class OrderAfterSave {
    protected LoggerInterface $logger;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Customer\Model\CustomerFactory $customerFactory;
    protected CustomerSession $customerSession;

    public function __construct(
        LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        CustomerSession $customerSession
    )
    {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }

    public function afterSave(\Magento\Sales\Api\OrderRepositoryInterface $orderRepo, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        try {
            if(!$this->customerSession->isLoggedIn() && !$order->getCustomerId()) {
                $password   = $this->customerSession->getCaPassword();
                $repassword = $this->customerSession->getCaRePassword();
                if (!empty($password) && !empty($repassword) && $password == $repassword) {
                    $websiteId = $this->storeManager->getWebsite()->getWebsiteId();

                    // Instantiate object (this is the most important part)
                    $customer = $this->customerFactory->create();
                    $customer->setWebsiteId($websiteId);

                    // Preparing data for new customer
                    $email = $order->getCustomerEmail();
                    $customer->setEmail($email);
                    $customer->setPrefix($order->getShippingAddress()->getPrefix());
                    $customer->setFirstname($order->getShippingAddress()->getFirstname());
                    $customer->setLastname($order->getShippingAddress()->getLastname());
                    $customer->setIsSubscribed(1);
                    $customer->setPassword($password);

                    // Save data
                    $customer->save();
                    $this->customerSession->setCustomerAsLoggedIn($customer);
                    $order->setCustomerId($customer->getId());
                    $order->setCustomerIsGuest(0);
                    $order->save();
                    $customer->sendNewAccountEmail();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info("Exception : " . $e->getMessage());
        } catch (\Error $e) {
            $this->logger->info("Error : ". $e->getMessage());
        }
        return $order;
    }
}
