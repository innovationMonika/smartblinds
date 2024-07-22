<?php

declare(strict_types=1);

namespace GoMage\Xsl\Model;

use GoMage\Xsl\Api\XslXmlGeneratorInterface;
use Magento\Framework\Xml\GeneratorFactory;
use SimpleXMLElement;

class XslXmlGenerator implements XslXmlGeneratorInterface
{
    private GeneratorFactory $generatorFactory;
    private ModuleXslApplier $moduleXslApplier;

    public function __construct(
        GeneratorFactory $generatorFactory,
        ModuleXslApplier $moduleXslApplier
    ) {
        $this->generatorFactory = $generatorFactory;
        $this->moduleXslApplier = $moduleXslApplier;
    }

    public function generateXml(string $moduleName, string $fileName, array $data): string
    {
        $this->convertNumbersToStrings($data);
        $xmlElement = $this->prepareXmlElement($data);
        return $this->moduleXslApplier->apply($moduleName, $fileName, $xmlElement);
    }

    private function convertNumbersToStrings(array &$data)
    {
        array_walk_recursive($data, function (&$item) {
            if (is_int($item) || is_double($item)) {
                $item = (string) $item;
            }
        });
    }

    private function prepareXmlElement(array $data): SimpleXMLElement
    {
        $generator = $this->generatorFactory->create();
        $generator->setIndexedArrayItemName('list');
        return simplexml_import_dom($generator->arrayToXml($data)->getDom());
    }
}
