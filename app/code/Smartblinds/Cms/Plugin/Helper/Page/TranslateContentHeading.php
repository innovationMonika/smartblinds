<?php declare(strict_types=1);

namespace Smartblinds\Cms\Plugin\Helper\Page;

use Magento\Cms\Helper\Page;
use Magento\Framework\App\ActionInterface;

class TranslateContentHeading
{
    public function afterPrepareResultPage(
        Page $subject,
        $resultPage,
        ActionInterface $action,
        $pageId = null
    ) {
        if (!$resultPage) {
            return $resultPage;
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $contentHeadingBlock = $resultPage->getLayout()->getBlock('page_content_heading');
        if ($contentHeadingBlock) {
            $contentHeadingBlock->setContentHeading(__($contentHeadingBlock->getContentHeading()));
        }
        return $resultPage;
    }
}
