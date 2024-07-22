<?php

namespace Smartblinds\Redirect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;

class RedirectObserver implements ObserverInterface
{
    protected $redirect;
    protected $actionFlag;
    protected $request;

    public function __construct(
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        RequestInterface $request
    ) {
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getRequestString() == '/rolgordijnen/?control_type=306' && $this->request->getParam('control_type') == 306) {
            $url = '/rolgordijnen/elektrisch/';
            // setting an action flag to stop processing further hierarchy
             $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
             /// redirecting to error page
             $observer->getControllerAction()->getResponse()->setRedirect($url);
             return $this;
        }
    }
}
