<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 *
 * Class provides configs for the extension
 */
class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * @var string
     */
    protected $pathPrefix = 'aminstagramfeed/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const XPATH_ENABLED = 'general/enabled';
    public const XPATH_INTERNAL_TOKEN = 'credentials/internal_token';
    public const CLIENT_ID = 'credentials/client_id';
    public const CLIENT_SECRET = 'credentials/client_secret';
    public const ACCESS_TOKEN = 'credentials/access_token_new';
    public const USER_ID = 'credentials/user_id';
    public const AUTHORIZE_HOST = 'credentials/authorize_host';

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        ReinitableConfigInterface $reinitableConfig,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($scopeConfig);
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::XPATH_ENABLED);
    }

    /**
     * @param null|int $storeId
     * @return mixed|string
     */
    public function getAccessToken($storeId = null)
    {
        if ($accessToken = $this->getValue(self::ACCESS_TOKEN, $storeId)) {
            $accessToken = $this->encryptor->decrypt($accessToken);
        }

        return $accessToken;
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getUserId($storeId = null)
    {
        return $this->getValue(self::USER_ID, $storeId);
    }

    /**
     * @param string $accessToken
     * @param null|int $storeId
     *
     * @return $this;
     */
    public function saveAccessToken($accessToken, $storeId = null)
    {
        $accessToken = $this->encryptor->encrypt($accessToken);
        if ($storeId) {
            $this->getConfigWriter()->save(
                $this->pathPrefix . self::ACCESS_TOKEN,
                $accessToken,
                ScopeInterface::SCOPE_STORES,
                $storeId
            );
        } else {
            $this->getConfigWriter()->save($this->pathPrefix . self::ACCESS_TOKEN, $accessToken);
        }

        $this->getReinitableConfig()->reinit();

        return $this;
    }

    /**
     * @param $userId
     * @param null $storeId
     *
     * @return $this
     */
    public function saveUserId($userId, $storeId = null)
    {
        if ($storeId) {
            $this->getConfigWriter()->save(
                $this->pathPrefix . self::USER_ID,
                $userId,
                ScopeInterface::SCOPE_STORES,
                $storeId
            );
        } else {
            $this->getConfigWriter()->save($this->pathPrefix . self::USER_ID, $userId);
        }

        $this->getReinitableConfig()->reinit();

        return $this;
    }

    /**
     * @return string
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * @return WriterInterface
     */
    public function getConfigWriter()
    {
        return $this->configWriter;
    }

    /**
     * @return ReinitableConfigInterface
     */
    public function getReinitableConfig()
    {
        return $this->reinitableConfig;
    }

    /**
     * @return mixed
     */
    public function getAuthorizeHost()
    {
        return $this->getValue(self::AUTHORIZE_HOST);
    }

    /**
     * @param bool $needGenerate = false
     * @return string
     */
    public function getInternalToken($needGenerate = false)
    {
        $token = $this->getValue(self::XPATH_INTERNAL_TOKEN);
        if ($needGenerate) {
            $token = hash('sha256', rand(1, PHP_INT_MAX));
            $this->getConfigWriter()->save($this->pathPrefix . self::XPATH_INTERNAL_TOKEN, $token);
            $this->getReinitableConfig()->reinit();
        }

        return $token;
    }
}
