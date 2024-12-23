<?php
/**
 * This file is part of the Awin AdvertiserTracking module
 *
 */
// declare(strict_types=1);

namespace Awin\AdvertiserTracking\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class AllPageTemplate extends Template
{
    /**
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->isSetFlag('awin_settings/general/awin_advertiser_id');
    }

    /*
     TODO:add method hasAwinQueryParameters for conditionally renedering the cookie pixel
    */ 
    
    /**
     * @return string
     */
    public function getImgUrl(): string
    {        
        $awc_from_url = $this->_request->getParam('awc');
        $source_from_url = $this->_request->getParam('source');
        return $this->getBaseUrl() . "awin/?awc=" . $awc_from_url . "&source=" . $source_from_url;
    }
    
    /**
     * Get advertiserId 
     *
     * @return string
     */
    public function getAdvertiserId(): string
    {
        return $this->getValue('awin_settings/general/awin_advertiser_id');
    }
    
    /**
     * Wrapper around `$this->_scopeConfig->isSetFlag` that ensures store scope is checked
     *
     * @param string $path
     * @return bool
     */
    private function isSetFlag(string $path): bool
    {
        return $this->_scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
    }

    /**
     * Wrapper around `$this->_scopeConfig->getValue` that ensures store scope is checked
     *
     * @param string $path
     * @return mixed
     */
    private function getValue(string $path)
    {        
        try {
            return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore());
        } catch (Exception $e) {
            return null;
        }
    }
    
}
