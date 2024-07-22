<?php declare(strict_types=1);

namespace Smartblinds\System\Block\System\Config\Form\Field\Column;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class AlternateControlType extends Select
{
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(
        Context $context,
        AttributeRepositoryInterface $attributeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeRepository = $attributeRepository;
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
        $attribute = $this->attributeRepository->get(
            Product::ENTITY,
            'control_type_alternate'
        );
        $options = [];
        foreach ($attribute->getOptions() as $option) {
            $options[] = [
                'label' => $option->getLabel(),
                'value' => $option->getValue()
            ];
        }
        return $options;
    }
}
