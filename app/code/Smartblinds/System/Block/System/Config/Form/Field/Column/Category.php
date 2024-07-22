<?php declare(strict_types=1);

namespace Smartblinds\System\Block\System\Config\Form\Field\Column;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Smartblinds\System\Model\Product\Attribute\Source\SystemCategory;

class Category extends Select
{
    private SystemCategory $systemCategory;

    public function __construct(
        Context $context,
        SystemCategory $systemCategory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->systemCategory = $systemCategory;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return $this->systemCategory->getAllOptions(false);
    }
}
