<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AlternateHreflang\Model\AlternateHreflang;

use Magefan\AlternateHreflang\Model\AbstractAlternateHreflang;
use Magefan\AlternateHreflang\Model\Config;

class CmsIndex extends AbstractAlternateHreflang
{
    /**
     * @param $id
     * @param $storeId
     * @return string
     */
    public function getObjectUrl($id, $storeId)
    {
        return parent::getObjectUrl($id, $storeId);
    }

    /**
     * @return string
     */
    protected function getObjectType()
    {
        return Config::PAGE_TITLE_HOMEPAGE;
    }

    /**
     * @param $currentObject
     * @return array
     */
    public function getAlternateUrls($currentObject)
    {
        $urls = [];
        $defaultStore = $this->storeManager->getDefaultStoreView();
        $currentStore = $this->storeManager->getStore();
        foreach ($this->storeManager->getStores() as $store) {
            if (!$store->isActive()) {
                continue;
            }
            if (!$this->isAvailableStoreGroup($store)) {
                continue;
            }

            $url = $store->getBaseUrl();
            if (/*$this->config->isStoreCodeInUrlEnabled() &&*/ $store->getId() == $defaultStore->getId()) {
                $removeStoreCode = false;
                if ($currentStore->getId() != $store->getId()) {
                    $removeStoreCode = true;
                } else {
                    $currentUrl = $this->request->getUriString();
                    $currentUrl = explode('?', $currentUrl);
                    $currentUrl = trim($currentUrl[0], '/');
                    if (mb_strpos($currentUrl, '/' . $store->getCode())
                        != mb_strlen($currentUrl) - mb_strlen($store->getCode()) - 1) {
                        $removeStoreCode = true;
                    }
                }

                if ($removeStoreCode && count(explode('/', trim($url, '/'))) > 3) {
                    $url = mb_substr($url, 0, -1 - mb_strlen($store->getCode()));
                }
            }

            $languageCode = $this->config->getLocaleCode($store->getId());
            $urls[$languageCode] = $url;
        }
        return $urls;
    }
}
