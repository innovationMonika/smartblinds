<?php

declare(strict_types = 1);

namespace GoMage\ErpOrderExport\Model;

use GoMage\Erp\Model\GeneralConfig;
use GoMage\Xsl\Api\XslXmlGeneratorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\Serializer\Json;

class Transport
{
    private CurlFactory $curlFactory;
    private GeneralConfig $generalConfig;
    private Config $config;
    private XslXmlGeneratorInterface $xslXmlGenerator;
    private Logger $logger;
    private Reader $moduleReader;
    private Json $json;

    private string $moduleName;
    private string $fileName;

    public function __construct(
        CurlFactory $curlFactory,
        GeneralConfig $generalConfig,
        Config $config,
        XslXmlGeneratorInterface $xslXmlGenerator,
        Logger $logger,
        Reader $moduleReader,
        Json $json,
        string $moduleName,
        string $fileName
    ) {
        $this->curlFactory = $curlFactory;
        $this->generalConfig = $generalConfig;
        $this->config = $config;
        $this->xslXmlGenerator = $xslXmlGenerator;
        $this->logger = $logger;
        $this->moduleReader = $moduleReader;
        $this->json = $json;
        $this->moduleName = $moduleName;
        $this->fileName = $fileName;
    }

    public function send(array $data)
    {
        $curl = $this->createCurl(['Content-Type' => 'application/xml']);

        $url = $this->config->getApiUrl();
        $xml = $this->xslXmlGenerator->generateXml($this->moduleName, $this->fileName, $data);

        try {
            $curl->post($url, $xml);
        } catch (\Exception $e) {}

        $this->log($curl, 'POST', $url, $xml);

        return $this->getResponse($curl);
    }

    public function get(array $data)
    {
        $curl = $this->createCurl();

        $query = implode(',', $data);
        $url = $this->config->getApiQueryUrl() . '?id=' . $query;

        $curl->get($url);

        $this->log($curl, 'GET', $url);

        return $this->getResponse($curl);
    }

    private function log(Curl $curl, string $method, string $url, string $data = '')
    {
        $this->logger->scheduleMessage(
            implode(PHP_EOL, [
                'REQUEST',
                "$method $url",
                'BODY',
                $data,
                'RESPONSE',
                "CODE {$curl->getStatus()}",
                'BODY',
                $this->getBody($curl) ?: ''
            ])
        );
    }

    private function getBody($curl)
    {
//        return $this->getFixture('accepted');
        return $curl->getBody();
    }

    private function getFixture(string $filename)
    {
        $etcDir = $this->moduleReader
            ->getModuleDir(Dir::MODULE_ETC_DIR, 'GoMage_ErpOrderExport');
        return file_get_contents("$etcDir/fixtures/$filename.xml");
    }

    private function createCurl(array $additionalHeaders = []): Curl
    {
        /** @var Curl $curl */
        $curl = $this->curlFactory->create();
        $headers = array_merge([
            'X-API-Key'    => $this->generalConfig->getApiKey(),
            'accept' => 'application/xml'
        ], $additionalHeaders);
        $curl->setHeaders($headers);
        $curl->setTimeout(30);
        return $curl;
    }

    private function getResponse(Curl $curl)
    {
        try {
            $responseXml = $this->getBody($curl);
            $response = $this->json->unserialize(
                $this->json->serialize(
                    simplexml_load_string($responseXml ?: '')
                )
            );
            return is_array($response) ? $response : [];
        } catch (\Exception $e) {}

        return [];
    }
}
