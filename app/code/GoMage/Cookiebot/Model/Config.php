<?php declare(strict_types=1);

namespace GoMage\Cookiebot\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'gomage_cookiebot/general/enabled',
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getCookieConsentUrl(): string
    {
        return (string) $this->scopeConfig->getValue(
            'gomage_cookiebot/cookie_consent/url',
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getCookieConsentId(): string
    {
        return (string) $this->scopeConfig->getValue(
            'gomage_cookiebot/cookie_consent/id',
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getCookieConsentCbid(): string
    {
        return (string) $this->scopeConfig->getValue(
            'gomage_cookiebot/cookie_consent/cbid',
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getCookieConsentBlockingMode(): string
    {
        return (string) $this->scopeConfig->getValue(
            'gomage_cookiebot/cookie_consent/blocking_mode',
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getCookieConsentCulture(): ?string
    {
        return $this->scopeConfig->getValue(
            'gomage_cookiebot/cookie_consent/culture',
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getCookieDeclarationUrl(): string
    {
        return (string) $this->scopeConfig->getValue(
            'gomage_cookiebot/cookie_declaration/url',
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    public function getCookieDeclarationId(): string
    {
        return (string) $this->scopeConfig->getValue(
            'gomage_cookiebot/cookie_declaration/id',
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
