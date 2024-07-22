<?php

declare(strict_types=1);

namespace GoMage\Xsl\Model;

use Magento\Framework\XsltProcessor\XsltProcessorFactory;
use SimpleXMLElement;

class XslApplier
{
    private XsltProcessorFactory $xsltProcessorFactory;

    public function __construct(XsltProcessorFactory $xsltProcessorFactory)
    {
        $this->xsltProcessorFactory = $xsltProcessorFactory;
    }

    public function apply(SimpleXMLElement $xsl, SimpleXMLElement $xml, array $params = []): string
    {
        $xsltProcessor = $this->xsltProcessorFactory->create();
        $xsltProcessor->setSecurityPrefs(XSL_SECPREF_DEFAULT | XSL_SECPREF_READ_NETWORK);
        $xsltProcessor->importStyleSheet($xsl);
        $xsltProcessor->registerPHPFunctions();

        foreach ($params as $name => $value) {
            $xsltProcessor->setParameter('', $name, $value);
        }

        return (string) $xsltProcessor->transformToXml($xml);
    }
}
