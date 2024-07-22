<?php declare(strict_types=1);

namespace Smartblinds\Cms\Block\System\Config\Form\Field\Column;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class ControlType extends Select
{
    /**
     * @var AttributeRepositoryInterface
     */
    private AttributeRepositoryInterface $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @param $value
     * @return ControlType
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSourceOptions(): array
    {
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'control_type');
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
