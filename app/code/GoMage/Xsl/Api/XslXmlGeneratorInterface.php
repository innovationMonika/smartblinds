<?php

declare(strict_types=1);

namespace GoMage\Xsl\Api;

interface XslXmlGeneratorInterface
{
    public function generateXml(string $moduleName, string $fileName, array $data): string;
}
