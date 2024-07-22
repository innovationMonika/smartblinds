<?php

declare(strict_types=1);

namespace GoMage\SamplesCategory\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class AddCategoryLayoutUpdateHandle implements ObserverInterface
{
    /**
     * Key name attribute value
     */
    const NAME_ATTRIBUTE = 'is_samples_category';

    /**
     * Layout Handle Name
     */
    const LAYOUT_HANDLE_NAME = 'catalog_category_view_samples_layout';

    /**
     * Action Name
     */
    const ACTION_NAME = 'catalog_category_view';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        $category = $this->registry->registry('current_category');
        if(static::ACTION_NAME === $event->getFullActionName()
            && $category && $category->getData(static::NAME_ATTRIBUTE)) {
            $layoutUpdate = $event->getLayout()->getUpdate();
            $layoutUpdate->addHandle(static::LAYOUT_HANDLE_NAME);
        }
    }
}
