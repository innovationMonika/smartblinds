<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Controller\Adminhtml\Post;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Api\PostRepositoryInterface;
use Amasty\InstagramFeed\Controller\Adminhtml\Post;
use Amasty\InstagramFeed\Model\PostFactory;
use Amasty\InstagramFeed\Model\ResourceModel\Post\Collection;
use Amasty\InstagramFeed\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

abstract class AbstractMassAction extends Post
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PostRepositoryInterface
     */
    protected $repository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PostFactory
     */
    protected $modelFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        PostRepositoryInterface $repository,
        CollectionFactory $collectionFactory,
        PostFactory $modelFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        $this->modelFactory = $modelFactory;
    }

    /**
     * Execute action for post
     *
     * @param PostInterface $post
     */
    abstract protected function itemAction(PostInterface $post);

    /**
     * Mass action execution
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection = $this->filter->getCollection($collection);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $collectionSize = $collection->getSize();
        if ($collectionSize) {
            try {
                $updatedCount = 0;
                /** @var PostInterface $model */
                foreach ($collection->getItems() as $model) {
                    try {
                        $this->itemAction($model);
                        $updatedCount++;
                    } catch (LocalizedException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                }

                $this->messageManager->addSuccessMessage($this->getSuccessMessage($updatedCount));
            } catch (CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($this->getErrorMessage());
                $this->logger->critical($e);
            }
        }

        return $resultRedirect->setRefererUrl();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        return __('We can\'t change item right now. Please review the log and try again.');
    }

    /**
     * @param int $collectionSize
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been changed.', $collectionSize);
        }

        return __('No records have been changed.');
    }
}
