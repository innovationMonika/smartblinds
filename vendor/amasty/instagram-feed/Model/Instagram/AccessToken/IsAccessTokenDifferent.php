<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

declare(strict_types=1);

namespace Amasty\InstagramFeed\Model\Instagram\AccessToken;

use Amasty\InstagramFeed\Model\ConfigProvider;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Check if different accounts are connected for different stores
 */
class IsAccessTokenDifferent
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     */
    public function execute(?int $storeId = null): bool
    {
        $storeId = $storeId === null ? $this->storeManager->getStore()->getId() : $storeId;

        return $this->configProvider->getAccessToken($storeId)
            != $this->configProvider->getAccessToken(Store::DEFAULT_STORE_ID);
    }
}
