<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Amasty\InstagramFeed\Test\Unit\Traits;
use Amasty\InstagramFeed\Model\ConfigProvider;

/**
 * Class ConfigProviderTest
 * phpcs:ignoreFile
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var \Amasty\InstagramFeed\Model\ConfigProvider|MockObject $model
     */
    private $model;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface|MockObject $configWriter
     */
    private $configWriter;

    public function setUp(): void
    {
        $this->model = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigWriter', 'getReinitableConfig'])
            ->getMock();
    }

    /**
     * @covers ConfigProvider::saveAccessToken
     */
    public function testSaveAccessToken()
    {
        $token = 'test';
        $storeId = 2;

        $reinitableConfig = $this->getObjectManager()->getObject(\Magento\Framework\App\ReinitableConfig::class);
        $this->model->expects($this->once())->method('getReinitableConfig')->willReturn($reinitableConfig);

        $configWriter = $this->getObjectManager()->getObject(\Magento\Framework\App\Config\Storage\Writer::class);
        $this->model->expects($this->once())->method('getConfigWriter')->willReturn($configWriter);

        $encrypter = $this->createMock(\Magento\Framework\Encryption\Encryptor::class);
        $this->setProperty($this->model, 'encryptor', $encrypter, ConfigProvider::class);
        $encrypter->expects($this->once())->method('encrypt')->willReturn('test');

        $this->model->saveAccessToken($token, $storeId);
    }
}
