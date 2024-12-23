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
use Amasty\InstagramFeed\Model\PostFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends Post
{
    /**
     * @var PostRepositoryInterface
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PostFactory
     */
    private $postFactory;

    public function __construct(
        Context $context,
        PostFactory $postFactory,
        PostRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->postFactory = $postFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $model = $this->getPostModel();
                $this->prepareData($data);
                $model->addData($data);
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('Post has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [PostInterface::POST_ID => $model->getId(), '_current' => true]
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                if ($postId = (int) $this->getRequest()->getParam(PostInterface::POST_ID)) {
                    $resultRedirect->setPath('*/*/edit', [PostInterface::POST_ID => $postId]);
                } else {
                    $resultRedirect->setPath('*/*/');
                }

                return $resultRedirect;
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return PostInterface|Post
     * @throws LocalizedException
     */
    protected function getPostModel()
    {
        /** @var Post $model */
        $model = $this->postFactory->create();

        if ($postId = (int) $this->getRequest()->getParam(PostInterface::POST_ID)) {
            $model = $this->repository->getById($postId);
            if ($postId != $model->getPostId()) {
                throw new LocalizedException(__('The wrong item is specified.'));
            }
        }

        return $model;
    }

    /**
     * @param array $data
     */
    private function prepareData(&$data)
    {
        foreach ($data as $key => $value) {
            if ($key == 'post_product_container') {
                $productData = array_shift($value);
                $data[PostInterface::PRODUCT_ID] = $productData['entity_id'] ?? null;
            }
            unset($data[$key]);
        }
        if (!isset($data[PostInterface::PRODUCT_ID])) {
            $data[PostInterface::PRODUCT_ID] = null;
        }
    }
}
