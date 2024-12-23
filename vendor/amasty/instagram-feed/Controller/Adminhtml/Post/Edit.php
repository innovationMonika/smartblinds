<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Controller\Adminhtml\Post;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Controller\Adminhtml\Post;
use Amasty\InstagramFeed\Model\ConfigProvider;
use Amasty\InstagramFeed\Model\Repository\PostRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Post
{
    /**
     * @var PostRepository
     */
    private $repository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        PostRepository $repository,
        ConfigProvider $configProvider,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->configProvider = $configProvider;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($postId = (int) $this->getRequest()->getParam(PostInterface::POST_ID)) {
            try {
                $post = $this->repository->getById($postId);
                /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
                $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $resultPage->getConfig()->getTitle()->prepend(__('Edit Post "%1"', $post->getIgId()));
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This post no longer exists.'));

                return $resultRedirect->setPath('*/*/index');
            }
        } else {
            return $resultRedirect->setPath('*/*/index');
        }

        return $resultPage;
    }
}
