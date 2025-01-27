<?php declare(strict_types=1);

namespace GoMage\Cookiebot\Plugin\Framework\View\Page\Config;

use GoMage\Cookiebot\Model\Config;
use Magento\Framework\View\Page\Config as PageConfig;

class AddCookieConsentPageAsset
{
    private Config $config;

    private bool $isCookieConsentScriptAdded = false;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function beforeAddPageAsset(PageConfig $subject)
    {
        if (!$this->isCookieConsentScriptAdded) {
            $this->addCookieConsentScript($subject);
        }
    }

    public function beforeAddRemotePageAsset(PageConfig $subject)
    {
        if (!$this->isCookieConsentScriptAdded) {
            $this->addCookieConsentScript($subject);
        }
    }

    private function addCookieConsentScript(PageConfig $pageConfig)
    {
        if (!$this->config->isEnabled()) {
            return;
        }
        $this->isCookieConsentScriptAdded = true;
        $attributes = [
            'id'                => $this->config->getCookieConsentId(),
            'data-cbid'         => $this->config->getCookieConsentCbid(),
            'data-blockingmode' => $this->config->getCookieConsentBlockingMode(),
            'async'             => true
        ];
        if ($culture = $this->config->getCookieConsentCulture()) {
            $attributes['data-culture'] = $culture;
        }
        $pageConfig->addRemotePageAsset(
            $this->config->getCookieConsentUrl(),
            'js',
            ['attributes' => $attributes]
        );
    }
}
