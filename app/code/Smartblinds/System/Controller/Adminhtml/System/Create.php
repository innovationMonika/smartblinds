<?php

namespace Smartblinds\System\Controller\Adminhtml\System;

class Create extends Base
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
