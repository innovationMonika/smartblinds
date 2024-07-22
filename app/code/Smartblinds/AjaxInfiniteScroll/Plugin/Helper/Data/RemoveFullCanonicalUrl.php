<?php declare(strict_types=1);

namespace Smartblinds\AjaxInfiniteScroll\Plugin\Helper\Data;

class RemoveFullCanonicalUrl extends \WeltPixel\AjaxInfiniteScroll\Helper\Data
{
    public function afterGetAjaxRefreshCanonicalUrl()
    {
        return '/ajaxcatalog/canonical/refresh/';
    }
}
