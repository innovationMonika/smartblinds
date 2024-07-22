<?php

declare(strict_types=1);

namespace GoMage\Xsl\Model;

use Magento\Framework\Module\Dir\Reader;
use function simplexml_load_file;
use SimpleXMLElement;

class ModuleXslReader
{
    private Reader $moduleDirReader;

    public function __construct(Reader $moduleDirReader)
    {
        $this->moduleDirReader = $moduleDirReader;
    }

    public function readContent(string $moduleName, string $fileName): SimpleXMLElement
    {
        $filePath = $this->moduleDirReader->getModuleDir('etc', $moduleName)
            . DIRECTORY_SEPARATOR . 'xsl' . DIRECTORY_SEPARATOR
            . $fileName;

        return simplexml_load_file($filePath);
    }
}
