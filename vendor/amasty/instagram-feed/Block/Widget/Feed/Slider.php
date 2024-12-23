<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Block\Widget\Feed;

use Amasty\InstagramFeed\Model\Instagram\Client;
use Magento\Framework\View\Element\Template;

/**
 * Class Slider
 *
 * Implements posts slider
 */
class Slider extends AbstractGrid
{
    /**
     * Default slick slider autoplay speed
     */
    public const DEFAULT_SLIDER_AUTOPLAY_SPEED = 2000;

    /**
     * @var string
     */
    protected $_template = 'Amasty_InstagramFeed::widget/feed/content/slider.phtml';

    /**
     * @return int
     */
    public function getColumnsCount()
    {
        return (int)$this->getData('columns_count') ?: 1;
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        return (int)$this->getData('rows_count') ?: 1;
    }

    /**
     * @return string
     */
    public function isAutoplayEnabled()
    {
        return (bool)$this->getData('autoplay') ? 'true' : 'false';
    }

    /**
     * @return int
     */
    public function getAutoplaySpeed()
    {
        if (!$this->hasData('autoplay_delay')) {
            $this->setData('autoplay_delay', self::DEFAULT_SLIDER_AUTOPLAY_SPEED);
        }

        return $this->getData('autoplay_delay');
    }
}
