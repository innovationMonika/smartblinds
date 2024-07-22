<?php

declare(strict_types=1);

namespace Smartblinds\TwoFactorAuth\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\UrlInterface;
use Magento\TwoFactorAuth\Controller\Adminhtml\Tfa\Index;
use Magento\TwoFactorAuth\Api\TfaInterface;
use Magento\TwoFactorAuth\Api\TfaSessionInterface;
use Magento\TwoFactorAuth\Api\UserConfigRequestManagerInterface;
use Magento\TwoFactorAuth\Controller\Adminhtml\Tfa\Requestconfig;
use Magento\TwoFactorAuth\Model\UserConfig\HtmlAreaTokenVerifier;

class HandleActionPredispatch
{
    private TfaInterface $tfa;
    private TfaSessionInterface $tfaSession;
    private UserConfigRequestManagerInterface $configRequestManager;
    private $action;
    private HtmlAreaTokenVerifier $tokenManager;
    private ActionFlag $actionFlag;
    private UrlInterface $url;
    private AuthorizationInterface $authorization;
    private UserContextInterface $userContext;
    private Config $config;

    public function __construct(
        TfaInterface $tfa,
        TfaSessionInterface $tfaSession,
        UserConfigRequestManagerInterface $configRequestManager,
        HtmlAreaTokenVerifier $tokenManager,
        ActionFlag $actionFlag,
        UrlInterface $url,
        AuthorizationInterface $authorization,
        UserContextInterface $userContext,
        Config $config
    ) {
        $this->tfa = $tfa;
        $this->tfaSession = $tfaSession;
        $this->configRequestManager = $configRequestManager;
        $this->tokenManager = $tokenManager;
        $this->actionFlag = $actionFlag;
        $this->url = $url;
        $this->authorization = $authorization;
        $this->userContext = $userContext;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        /** @var $controllerAction AbstractAction */
        $controllerAction = $observer->getEvent()->getData('controller_action');
        $this->action = $controllerAction;
        $fullActionName = $observer->getEvent()->getData('request')->getFullActionName();
        $userId = $this->userContext->getUserId();

        $this->tokenManager->readConfigToken();

        if (in_array($fullActionName, $this->tfa->getAllowedUrls(), true)) {
            //Actions that are used for 2FA must remain accessible.
            return;
        }

        if (in_array($userId, $this->config->getUsersWithDisabled2Fa())) {
            return;
        }

        if ($userId) {
            $configurationStillRequired = $this->configRequestManager->isConfigurationRequiredFor($userId);
            $toActivate = $this->tfa->getProvidersToActivate($userId);
            $toActivateCodes = [];
            foreach ($toActivate as $toActivateProvider) {
                $toActivateCodes[] = $toActivateProvider->getCode();
            }
            $accessGranted = $this->tfaSession->isGranted();

            if (!$accessGranted && $configurationStillRequired) {
                //User needs special link with a token to be allowed to configure 2FA
                if ($this->authorization->isAllowed(Requestconfig::ADMIN_RESOURCE)) {
                    $this->redirect('tfa/tfa/requestconfig');
                } else {
                    $this->redirect('tfa/tfa/accessdenied');
                }
            } else {
                if (!$accessGranted) {
                    if ($this->authorization->isAllowed(Index::ADMIN_RESOURCE)) {
                        $this->redirect('tfa/tfa/index');
                    } else {
                        $this->redirect('tfa/tfa/accessdenied');
                    }
                }
            }
        }
    }

    private function redirect(string $url): void
    {
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        $this->action->getResponse()->setRedirect($this->url->getUrl($url));
    }
}
