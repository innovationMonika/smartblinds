<?php

declare(strict_types=1);

namespace GoMage\Xsl\Model;

use SimpleXMLElement;

class ModuleXslApplier
{
    private ModuleXslReader $moduleXslReader;
    private XslApplier $xslApplier;

    public function __construct(
        ModuleXslReader $moduleXslReader,
        XslApplier $xslApplier
    ) {
        $this->moduleXslReader = $moduleXslReader;
        $this->xslApplier = $xslApplier;
    }

    public function apply(string $moduleName, string $fileName, SimpleXMLElement $xml, array $params = []): string
    {
        $xsl = $this->moduleXslReader->readContent($moduleName, $fileName);
        return $this->xslApplier->apply($xsl, $xml, $params);
    }
}
