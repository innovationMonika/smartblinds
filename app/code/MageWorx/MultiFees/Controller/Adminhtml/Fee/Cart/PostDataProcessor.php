<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Cart;

use MageWorx\MultiFees\Controller\Adminhtml\Fee\AbstractPostDataProcessor;

class PostDataProcessor extends AbstractPostDataProcessor
{
    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    public function filter($data)
    {
        return $this->filterCommonData($data);
    }
}
