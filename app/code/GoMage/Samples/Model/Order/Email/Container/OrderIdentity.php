<?php declare(strict_types=1);

namespace GoMage\Samples\Model\Order\Email\Container;

use Magento\Sales\Model\Order\Email\Container\Container;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Store\Model\ScopeInterface;

class OrderIdentity extends Container implements IdentityInterface
{
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            $this->getConfigPath('enabled'),
            ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    public function getEmailCopyTo()
    {
        $data = $this->getConfigValue($this->getConfigPath('copy_to'), $this->getStore()->getStoreId());
        if (!empty($data)) {
            return array_map('trim', explode(',', $data));
        }
        return false;
    }

    public function getCopyMethod()
    {
        return $this->getConfigValue($this->getConfigPath('copy_method'), $this->getStore()->getStoreId());
    }

    public function getGuestTemplateId()
    {
        return $this->getConfigValue($this->getConfigPath('guest_template'), $this->getStore()->getStoreId());
    }

    public function getTemplateId()
    {
        return $this->getConfigValue($this->getConfigPath('template'), $this->getStore()->getStoreId());
    }

    public function getEmailIdentity()
    {
        return $this->getConfigValue($this->getConfigPath('identity'), $this->getStore()->getStoreId());
    }

    private function getConfigPath(string $field): string
    {
        return "gomage_samples/order_email/$field";
    }
}
