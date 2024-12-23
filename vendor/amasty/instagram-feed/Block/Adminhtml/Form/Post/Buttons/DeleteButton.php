<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Block\Adminhtml\Form\Post\Buttons;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Block\Adminhtml\Form\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getPostId()) {
            return [];
        }

        $alertMessage = __('Are you sure you want to do this?');
        $onClick = sprintf('deleteConfirm("%s", "%s")', $alertMessage, $this->getDeleteUrl());

        $data = [
            'label' => __('Unlink'),
            'class' => 'delete',
            'id' => 'post-edit-delete-button',
            'on_click' => $onClick,
            'sort_order' => 10,
        ];

        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/unlink', [PostInterface::POST_ID => $this->getPostId()]);
    }

    /**
     * @return null|int
     */
    public function getPostId()
    {
        return (int) $this->request->getParam(PostInterface::POST_ID);
    }
}
