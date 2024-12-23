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
 * Class Collage
 *
 * Implements collage of posts
 */
class Collage extends AbstractGrid
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_InstagramFeed::widget/feed/content/collage.phtml';

    /**
     * @return int
     */
    protected function getPostLimit()
    {
        $size = (int)$this->getData('collage_size');
        return $size * $size;
    }
}
