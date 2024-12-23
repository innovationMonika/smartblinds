<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Controller\Adminhtml\Post;

use Amasty\InstagramFeed\Api\Data\PostInterface;

class MassUnlink extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    protected function itemAction(PostInterface $post)
    {
        $this->repository->unlink($post);
    }
}
