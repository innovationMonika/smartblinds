<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Test\Unit\Plugin\Config\Model\Config\Structure\Element\Dependency;

use PHPUnit\Framework\MockObject\MockObject;
use Amasty\InstagramFeed\Test\Unit\Traits;
use Amasty\InstagramFeed\Plugin\Config\Model\Config\Structure\Element\Dependency\FieldFactoryPlugin;

/**
 * Class FieldFactoryPluginTest
 * phpcs:ignoreFile
 */
class FieldFactoryPluginTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var FieldFactoryPlugin|MockObject $model
     */
    private $model;

    public function setUp(): void
    {
        $this->model = $this->getMockBuilder(FieldFactoryPlugin::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    /**
     * @covers FieldFactoryPlugin::isNeedProcess
     *
     * @dataProvider isNeedProcessDataProvider
     */
    public function testIsNeedProcess($data, $expected)
    {
        $result = $this->invokeMethod($this->model, 'isNeedProcess', [$data]);

        $this->assertEquals($expected, $result);
    }

    /**
     * DataProvider for testIsNeedProcess
     *
     * @return array
     */
    public function isNeedProcessDataProvider()
    {
        return [
            [['fieldData' => ['value1' => 5]], false],
            [['fieldData' => ['value' => ['test']]], false],
            [['fieldData' => ['value' => false]], false],
            [['fieldData' => ['value' => 'test']], false],
            [['fieldData' => ['value' => 'am_array:test']], true]
        ];
    }
}
