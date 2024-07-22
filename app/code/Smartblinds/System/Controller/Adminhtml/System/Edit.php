<?php declare(strict_types=1);

namespace Smartblinds\System\Controller\Adminhtml\System;

class Edit extends Base
{
    public function execute()
    {
        $system = $this->initSystem();

        $id = $system->getId();
        $title = $id ? __('Edit System') : __('New System');

        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = parent::execute();

        $this->initPage($page)->addBreadcrumb($title, $title);
        $page->getConfig()->getTitle()->prepend(__('Systems'));
        $page->getConfig()->getTitle()->prepend($id ? $system->getName() : __('New System'));
        return $page;
    }
}
