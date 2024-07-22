<?php declare(strict_types=1);

namespace GoMage\Ui\Model\Source;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AttributeOptions implements OptionSourceInterface
{
    private AttributeRepositoryInterface $attributeRepository;
    private string $entityType;
    private string $attributeCode;
    private bool $withEmpty;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        string $entityType,
        string $attributeCode,
        bool $withEmpty
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->entityType = $entityType;
        $this->attributeCode = $attributeCode;
        $this->withEmpty = $withEmpty;
    }

    public function toOptionArray()
    {
        try {
            $attribute = $this->attributeRepository->get($this->entityType, $this->attributeCode);
        } catch (NoSuchEntityException $e) {
            return [];
        }

        $options = $attribute->getOptions();
        if (!$this->withEmpty) {
            $options = array_filter($options, function ($option) {
                return $option->getValue();
            });
        }

        return array_map(function ($option) {
            return [
                'label' => $option->getLabel(),
                'value' => $option->getValue()
            ];
        }, $options);
    }
}
