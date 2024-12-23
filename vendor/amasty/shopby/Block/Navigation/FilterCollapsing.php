<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Layer\GetFiltersExpanded;
use Magento\Framework\View\Element\Template\Context;

class FilterCollapsing extends \Magento\Framework\View\Element\Template
{
    /**
     * @var GetFiltersExpanded
     */
    private $getFiltersExpanded;

    public function __construct(
        GetFiltersExpanded $getFiltersExpanded,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getFiltersExpanded = $getFiltersExpanded;
    }

    /**
     * @return int[]
     */
    public function getFiltersExpanded(): array
    {
        return $this->getFiltersExpanded->execute();
    }
}
