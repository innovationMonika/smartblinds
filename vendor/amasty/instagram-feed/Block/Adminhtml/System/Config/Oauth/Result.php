<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Block\Adminhtml\System\Config\Oauth;

use Amasty\InstagramFeed\Model\ConfigProvider;
use Magento\Backend\Block\Template;
use Magento\Store\Model\Store;

class Result extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_InstagramFeed::system/config/oauth/result.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function getButtonMessage()
    {
        return $this->getSuccess() ? __('Re-generate Access Token') : __('Generate Access Token');
    }

    public function getErrorMessage(): ?string
    {
        return (string) $this->getMessage();
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->configProvider->getUserId($this->getStoreId());
    }

    public function isTokenGenerated(): bool
    {
        return !$this->getErrorMessage() && $this->configProvider->getAccessToken($this->getStoreId());
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->configProvider->getAccessToken($this->getStoreId());
    }

    public function isGenerateImagesEnabled(): bool
    {
        return $this->getStoreId() == Store::DEFAULT_STORE_ID;
    }
}
