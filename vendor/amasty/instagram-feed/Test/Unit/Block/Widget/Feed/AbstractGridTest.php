<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Test\Unit\Block\Widget\Feed;

use PHPUnit\Framework\MockObject\MockObject;
use Amasty\InstagramFeed\Test\Unit\Traits;
use Amasty\InstagramFeed\Block\Widget\Feed\AbstractGrid;

/**
 * Class AbstractGridTest
 * phpcs:ignoreFile
 */
class AbstractGridTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var AbstractGrid|MockObject $block
     */
    private $block;

    public function setUp(): void
    {
        $this->block = $this->getMockBuilder(AbstractGrid::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getPostSize'])
            ->getMock();
    }

    /**
     * @covers AbstractGrid::getTitle
     */
    public function testGetTitle()
    {
        $value = 'test';
        $this->block->expects($this->once())->method('getData')->willReturn($value);
        $this->assertEquals($value, $this->block->getTitle());
    }

    /**
     * @covers AbstractGrid::getImageWidth
     */
    public function testGetImageWidth()
    {
        $post = ['images' => ['one_resolution' => ['width' => 150], 'two_resolution' => ['width' => 200]]];
        $this->block->expects($this->any())->method('getPostSize')->willReturn('one_resolution');
        $this->assertEquals(150, $this->block->getImageWidth($post));

        unset($post['images']['one_resolution']);
        $this->assertEquals('', $this->block->getImageWidth($post));
    }

    /**
     * @covers AbstractGrid::getPostData
     */
    public function testGetPostData()
    {
        $post = ['images' => 'test'];
        $key = 'images';
        $this->assertEquals('test', $this->block->getPostData($post, $key));

        $key = 'images1';
        $this->assertEquals('', $this->block->getPostData($post, $key));
    }

    /**
     * @covers AbstractGrid::getPostLimit
     */
    public function testGetPostLimit()
    {
        $value = '10';
        $this->block->expects($this->once())->method('getData')->willReturn($value);
        $this->assertEquals(10, $this->invokeMethod(
            $this->block,
            'getPostLimit',
            []
        ));
    }
}
