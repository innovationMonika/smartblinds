<?php

namespace Smartblinds\Config\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Tabs implements ArgumentInterface
{
    private $invisibleSections;

    public function __construct(
        array $invisibleSections = []
    ) {
        $this->invisibleSections = $invisibleSections;
    }

    public function getSectionStyles($section)
    {
        $styles = [];
        if (!$this->isSectionVisible($section)) {
            $styles[] = 'display: none;';
        }
        $styles = implode('', $styles);
        return $styles ? ' style="' . $styles . '"' : '';
    }

    public function isSectionVisible($section)
    {
        return !in_array($section->getId(), $this->invisibleSections);
    }
}
