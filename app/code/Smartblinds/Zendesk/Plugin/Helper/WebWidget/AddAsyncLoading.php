<?php declare(strict_types=1);

namespace Smartblinds\Zendesk\Plugin\Helper\WebWidget;

class AddAsyncLoading
{
    public function afterGetWebWidgetSnippet(
        \Zendesk\Zendesk\Helper\WebWidget $subject,
        $result
    ) {
        return str_replace(
            '<script ',
            '<script async="true" ',
            $result
        ).'<script type="text/javascript">
  window.zESettings = {
    webWidget: {
      chat: {
        departments: {
          enabled: []
        }
      }
    }
  };
</script>';
    }
}
