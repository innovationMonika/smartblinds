<?php declare(strict_types=1);

namespace Smartblinds\System\Block\System\Config\Form\Field\Column;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Smartblinds\System\Model\Source\System as SystemSource;

class System extends Select
{
    private SystemSource $source;

    public function __construct(
        Context $context,
        SystemSource $source,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->source = $source;
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
        return $this->source->toOptionArray();
    }
}
