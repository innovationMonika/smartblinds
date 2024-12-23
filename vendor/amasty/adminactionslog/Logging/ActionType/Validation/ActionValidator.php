<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Magento\Framework\DataObject;

class ActionValidator implements ActionValidatorInterface
{
    /**
     * @var DataObject
     */
    private $actionsList;

    public function __construct(DataObject $actionsList)
    {
        $this->actionsList = $actionsList;
    }

    public function isValid(MetadataInterface $metadata): bool
    {
        return !in_array($metadata->getRequest()->getFullActionName(), $this->actionsList->getList());
    }
}
