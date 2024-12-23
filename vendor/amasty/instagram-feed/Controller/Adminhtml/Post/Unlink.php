<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Controller\Adminhtml\Post;

use Amasty\InstagramFeed\Api\PostRepositoryInterface;
use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Controller\Adminhtml\Post;
use Amasty\CustomTabs\Model\Source\Type;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Unlink extends Post
{
    /**
     * @var PostRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PostRepositoryInterface $repository,
        LoggerInterface $logger,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($postId = $this->getRequest()->getParam(PostInterface::POST_ID)) {
            try {
                $post = $this->repository->getById($postId);
                $this->repository->unlink($post);
                $this->messageManager->addSuccessMessage(__('You have unlinked the post.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t unlink item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
