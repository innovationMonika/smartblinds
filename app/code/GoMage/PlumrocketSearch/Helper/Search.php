<?php

namespace GoMage\PlumrocketSearch\Helper;

class Search extends \Plumrocket\Search\Helper\Search
{
    /**
     * @return bool
     */
    public function allowedLogic()
    {
        $prFilterEnabled = $this->_moduleManager->isOutputEnabled('Plumrocket_ProductFilter');
        $ajaxRequest = $this->isAjaxRequest();
        if (!$ajaxRequest || $prFilterEnabled) {
            return false;
        }
        return true;
    }
}
