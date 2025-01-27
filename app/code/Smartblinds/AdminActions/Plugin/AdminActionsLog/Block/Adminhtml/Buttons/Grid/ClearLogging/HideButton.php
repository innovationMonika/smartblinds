<?php

namespace Smartblinds\AdminActions\Plugin\AdminActionsLog\Block\Adminhtml\Buttons\Grid\ClearLogging;

use Amasty\AdminActionsLog\Block\Adminhtml\Buttons\Grid\ClearLogging;

class HideButton
{
    public function afterGetButtonData(
        ClearLogging $subject,
        $result
    ) {
        return [];
    }
}
