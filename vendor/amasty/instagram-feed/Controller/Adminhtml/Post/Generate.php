<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

declare(strict_types=1);

namespace Amasty\InstagramFeed\Controller\Adminhtml\Post;

use Amasty\InstagramFeed\Model\Instagram\Management as PostManagement;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;

class Generate extends Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_InstagramFeed::post';

    /**
     * @var PostManagement
     */
    private $postManagement;

    public function __construct(
        PostManagement $postManagement,
        Context $context
    ) {
        parent::__construct($context);
        $this->postManagement = $postManagement;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $this->postManagement->update();

        return $this->resultRedirectFactory->create()->setRefererUrl();
    }
}
