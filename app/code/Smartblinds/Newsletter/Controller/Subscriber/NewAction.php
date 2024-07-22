<?php declare(strict_types=1);

namespace Smartblinds\Newsletter\Controller\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Smartblinds\Newsletter\Model\AdditionalData;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    private SubscriptionManagerInterface $subscriptionManager;
    private AdditionalData $additionalData;

    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        SubscriptionManagerInterface $subscriptionManager,
        AdditionalData $additionalData,
        EmailValidator $emailValidator = null
    ) {
        $this->subscriptionManager = $subscriptionManager;
        $this->additionalData = $additionalData;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customerAccountManagement,
            $subscriptionManager,
            $emailValidator
        );
    }

    public function execute()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
                /** @var Subscriber $subscriber */
                $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
                if ($subscriber->getId()
                    && (int)$subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
                    throw new LocalizedException(
                        __('This email address is already subscribed.')
                    );
                }

                $this->additionalData->setData('page', $this->getRequest()->getParam('page'));

                $storeId = (int)$this->_storeManager->getStore()->getId();
                $currentCustomerId = $this->getSessionCustomerId($email);
                $subscriber = $currentCustomerId
                    ? $this->subscriptionManager->subscribeCustomer($currentCustomerId, $storeId)
                    : $this->subscriptionManager->subscribe($email, $storeId);
                $message = $this->getSuccessMessage((int)$subscriber->getSubscriberStatus());
                $this->messageManager->addSuccessMessage($message);
            } catch (LocalizedException $e) {
                $this->messageManager->addComplexErrorMessage(
                    'localizedSubscriptionErrorMessage',
                    ['message' => $e->getMessage()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong with the subscription.'));
            }
        }
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_redirect->getRedirectUrl();
        return $redirect->setUrl($redirectUrl);
    }

    private function getSessionCustomerId(string $email): ?int
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return null;
        }

        $customer = $this->_customerSession->getCustomerDataObject();
        if ($customer->getEmail() !== $email) {
            return null;
        }

        return (int)$this->_customerSession->getId();
    }

    private function getSuccessMessage(int $status): Phrase
    {
        if ($status === Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }

        return __('Thank you for your subscription.');
    }
}
