<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types = 1);

namespace Magefan\WebP\Plugin\Magento\Framework\App\Response;

use Magefan\WebP\Api\HtmlParserInterface;
use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Model\Config;

/**
 * Class WebapiHttpInterface
 */
class WebapiHttpInterface
{
    /**
     * @var CreateWebPImageInterface
     */
    private $createWebPImage;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HtmlParserInterface
     */
    private $htmlParser;

    /**
     * @var GetWebPUrlInterface
     */
    private $webPUrl;

    /**
     * WebapiHttpInterface constructor.
     * @param GetWebPUrlInterface $webPUrl
     * @param HtmlParserInterface $htmlParser
     * @param CreateWebPImageInterface $createWebPImage
     * @param Config $config
     */
    public function __construct(
        GetWebPUrlInterface $webPUrl,
        HtmlParserInterface $htmlParser,
        CreateWebPImageInterface $createWebPImage,
        Config $config
    ) {
        $this->webPUrl = $webPUrl;
        $this->htmlParser = $htmlParser;
        $this->createWebPImage = $createWebPImage;
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\App\Response\HttpInterface $subject
     * @param $content
     * @return false|mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetContent(\Magento\Framework\App\Response\HttpInterface $subject, $content)
    {
        if (!is_string($content) || !$this->config->isEnabled() || !$this->config->isConvertImagesInRestApiRequests() /* || !$this->config->isBrowserWebPCompatible()*/) {
            return $content;
        } else {
            $isXml = (strpos($content, '<?xml') === 0);

            if ($isXml) {
                $json = new \SimpleXMLElement($content);
                $jsonString = (string)$json;
            } else {
                $jsonString = $content;
            }

            $result = [];
            if (is_string($jsonString)) {
                try {
                    $result = json_decode($jsonString, true);
                } catch (\Exception $e) {
                    $result = [];
                }
            }
            if ($result) {
                $resultHash = sha1(json_encode($result));
                $result = $this->replaceLinks($result);
                $response = json_encode($result);

                if (sha1($response) != $resultHash) {
                    if ($isXml) {
                        $content = "<?xml version='1.0'?>\n" .
                            "<response>$response</response>";
                    } else {
                        $content = $response;
                    }
                }
            }

            return $content;
        }
    }

    /**
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function replaceLinks($result) //: array need to check all posible return values (sting/array)
    {
        if (is_array($result)) {
            foreach ($result as $key => $value) {
                if (is_array($value)) {
                    $value = $this->replaceLinks($value);
                } elseif (is_string($value)) {
                    if (strpos((string)$value, '<img')) {
                        $value = $this->htmlParser->execute($value);
                    } else {
                        $allowedImageFormats = ['jpg', 'png', 'gif', 'jpeg'];
                        $ext = substr((string)strrchr($value, '.'), 1);
                        if (in_array($ext, $allowedImageFormats)) {
                            if ($this->createWebPImage->execute($value)) {
                                $value = $this->webPUrl->execute($value);
                            }
                        }
                    }
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
