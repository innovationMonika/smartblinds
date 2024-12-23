<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Block\Widget\Feed;

use Amasty\InstagramFeed\Model\ConfigProvider;
use Amasty\InstagramFeed\Model\Config\Source\Type;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface as Widget;

/**
 * Class Wrapper
 *
 * Wrapper for widget
 */
class Wrapper extends Template implements Widget
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        ModuleManager $moduleManager,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
        $this->configProvider = $configProvider;
    }

    /**
     * Determine if the block scope is private or public.
     * //TODO deprecated - remove in new versions
     *
     * @return bool
     */
    public function isScopePrivate()
    {
        if ($this->getData('feed_type') != Type::SINGLE) {
            $this->_isScopePrivate = true;

        }
        return $this->_isScopePrivate;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _toHtml()
    {
        $html = '';
        if ($this->moduleManager->isEnabled('Amasty_InstagramFeed') && $this->configProvider->isEnabled()) {
            $class = $this->getClassByType($this->getData('feed_type'));

            /** @var AbstractGrid $feed */
            $feed = $this->getLayout()->createBlock(
                $class
            )->addData(
                $this->getData()
            );
            $html = $feed->toHtml();
        }

        return $html;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo = array_merge($keyInfo, $this->getData());

        return $keyInfo;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getClassByType($type)
    {
        switch ($type) {
            case Type::SLIDER:
                $class = Slider::class;
                break;
            case Type::COLLAGE:
                $class = Collage::class;
                break;
            case Type::SINGLE:
                $class = Single::class;
                break;
            case Type::GRID:
            default:
                $class = Grid::class;
                break;
        }

        return $class;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->getData('cache_lifetime');
    }
}
