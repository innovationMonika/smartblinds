<?php declare(strict_types=1);

namespace Smartblinds\System\Block\System\Config\Form\Field\Column;

use Magento\Config\Model\Config\Source\Store as StoreSource;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Store extends Select
{
    private StoreSource $storeSource;

    public function __construct(
        Context $context,
        StoreSource $storeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeSource = $storeSource;
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
        return $this->storeSource->toOptionArray();
    }
}
