<?php declare(strict_types=1);

namespace Smartblinds\System\Controller\Adminhtml\System;

use GoMage\Ui\Model\EntityRegistry;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Smartblinds\System\Model\ResourceModel\System as SystemResource;
use Smartblinds\System\Model\System;
use Smartblinds\System\Model\SystemFactory;

abstract class Base extends Action
{
    const ADMIN_RESOURCE = 'Smartblinds_System::system';

    protected SystemResource $systemResource;

    protected SystemFactory $systemFactory;
    private EntityRegistry $entityRegistry;

    public function __construct(
        SystemResource $systemResource,
        SystemFactory $systemFactory,
        EntityRegistry $entityRegistry,
        Context $context
    ) {
        $this->systemResource = $systemResource;
        $this->systemFactory = $systemFactory;
        $this->entityRegistry = $entityRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

    protected function initSystem(): System
    {
        $id = $this->getRequest()->getParam('id');
        $system = $this->systemFactory->create();

        if ($id) {
            $this->systemResource->load($system, $id);
        }

        $this->entityRegistry->set('smartblinds_system', $system);

        return $system;
    }

    protected function initPage(Page $resultPage): Page
    {
        return $resultPage
            ->setActiveMenu('Smartblinds_System::systems')
            ->addBreadcrumb(__('Systems'), __('Systems'));
    }
}
