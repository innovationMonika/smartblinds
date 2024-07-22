<?php

namespace Smartblinds\Blog\Model;

use Magento\Framework\Model\AbstractModel;

class Url extends \Magefan\Blog\Model\Url
{
    public function getCanonicalUrl(AbstractModel $object)
    {
        if ($object->getData('parent_category')) {
            $object = clone $object;
            $object->setData('parent_category', null);
        }

        $storeIds = $object->getStoreIds();
        $useOtherStore = false;
        $currentStore = $this->_storeManager->getStore($object->getStoreId());

        if (is_array($storeIds)) {
            if (in_array(0, $storeIds)) {
                $storesIds = $currentStore->getGroup()->getStoreIds();
                foreach ($storesIds as $storeId) {
                    if ($currentStore->getId() == $storeId) {
                        $store = $this->_storeManager->getStore($storeId);
                        $useOtherStore = true;
                        $newStore = $store;
                        break;
                    }
                }

                if ($useOtherStore === false) {
                    $newStore = $currentStore->getGroup()->getDefaultStore();
                }
            } else {
                foreach ($storeIds as $storeId) {
                    $store = $this->_storeManager->getStore($storeId);
                    $useOtherStore = true;
                    $newStore = $store;
                    break;
                }
            }
        }

        $newStore = $this->getStoreForCorrectCanonicalTag($currentStore, $storeIds, $newStore);

        $storeChanged = false;
        if ($useOtherStore) {
            $scope = $this->_url->getScope();
            if ($scope && $newStore->getId() != $scope->getId()) {
                $this->startStoreEmulation($newStore);
                $storeChanged = true;
            }
        }

        $url = $this->getUrl($object, $object->getControllerName());

        if ($storeChanged) {
            $this->stopStoreEmulation();
        }

        return $url;
    }

    private function getStoreForCorrectCanonicalTag($currentStore, $storeIds, $newStore)
    {
        return in_array($currentStore->getId(), $storeIds) ? $currentStore : $newStore;
    }
}
