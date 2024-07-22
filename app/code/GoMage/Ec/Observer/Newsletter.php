<?php

namespace GoMage\Ec\Observer;

use Magento\Framework\Event\Observer as EventObserver;

class Newsletter extends \Anowave\Ec\Observer\Newsletter
{
    /**
     * Execute (non-PHPdoc)
     *
     * @see \Magento\Framework\Event\ObserverInterface::execute()
     */
    public function execute(EventObserver $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $subscriberStatus = $subscriber->getSubscriberStatus();

        $this->session->setNewsletterEvent($this->jsonHelper->encode(
            [
                'event' 			=> 'newsletterSubmit',
                'eventCategory' 	=> __('Newsletter'),
                'eventAction' 		=> __('Submit'),
                'eventLabel' 		=> __(($subscriberStatus == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED) ? 'Subscribe' : 'Unsubscribe'),
                'eventValue' 		=> 1
            ]));
    }
}
