<?php

namespace Smartblinds\AdminActions\Plugin\Backend\Block\Menu;

class HideItem
{
    public function afterRenderNavigation(
        \Magento\Backend\Block\Menu $subject,
        string $result,
        $menu,
        $level = 0,
        $limit = 0,
        $colBrakes = []
    ) {
        $result = str_replace(
            'id="menu-amasty-adminactionslog-container"',
            'id="menu-amasty-adminactionslog-container" style="display:none;"',
            $result
        );

        $result = str_replace(
            'data-ui-id="menu-amasty-adminactionslog-amaudit"',
            'data-ui-id="menu-amasty-adminactionslog-amaudit" style="display:none;"',
            $result
        );

        return $result;
    }
}
