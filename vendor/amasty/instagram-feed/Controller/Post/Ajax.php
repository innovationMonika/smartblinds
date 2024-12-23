<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Controller\Post;

use Amasty\InstagramFeed\Block\Widget\Feed\Grid;
use Amasty\InstagramFeed\Model\Instagram\Operation\CreatePostFromData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutInterface;

class Ajax extends Action
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var CreatePostFromData
     */
    private $createPostFromData;

    public function __construct(
        CreatePostFromData $createPostFromData,
        LayoutInterface $layout,
        Context $context
    ) {
        parent::__construct($context);
        $this->layout = $layout;
        $this->createPostFromData = $createPostFromData;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|null
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $html = '';

        if ($data = $this->getRequest()->getParam('data')) {
            $blockData = $this->getRequest()->getParam('block_data');
            /** @var Grid $gridBlock */
            $gridBlock = $this->layout->createBlock(Grid::class);
            $gridBlock->setAjaxPosts($this->createPostFromData->execute($data));
            $gridBlock->addData($blockData);
            $html = $gridBlock->toHtml();
        }
        $result->setContents($html);

        return $result;
    }
}
