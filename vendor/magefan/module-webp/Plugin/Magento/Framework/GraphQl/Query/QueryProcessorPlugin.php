<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\WebP\Plugin\Magento\Framework\GraphQl\Query;

/*
Comment for Magento 2.2.x as it does not has GraphQl
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema;
*/
use Magefan\WebP\Api\HtmlParserInterface;
use Magefan\WebP\Api\GetWebPUrlInterface;
use Magefan\WebP\Api\CreateWebPImageInterface;
use Magefan\WebP\Model\Config;
use Magefan\WebP\Model\Parser\PageBuilder as PageBuilderParser;

/**
 * Class QueryProcessorPlugin (only for graphql)
 */
class QueryProcessorPlugin
{
    /**
     * @var HtmlParserInterface
     */
    private $htmlParser;

    /**
     * @var GetWebPUrlInterface
     */
    private $webPUrl;

    /**
     * @var CreateWebPImageInterface
     */
    private $createWebPImage;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PageBuilderParser
     */
    private $pageBuilderParser;

    /**
     * QueryProcessorPlugin constructor.
     * @param HtmlParserInterface $htmlParser
     * @param GetWebPUrlInterface $webPUrl
     * @param CreateWebPImageInterface $createWebPImage
     * @param Config $config
     */
    public function __construct(
        HtmlParserInterface $htmlParser,
        GetWebPUrlInterface $webPUrl,
        CreateWebPImageInterface $createWebPImage,
        Config $config,
        PageBuilderParser $pageBuilderParser
    ) {
        $this->htmlParser = $htmlParser;
        $this->webPUrl = $webPUrl;
        $this->createWebPImage = $createWebPImage;
        $this->config = $config;
        $this->pageBuilderParser = $pageBuilderParser;
    }
    /**
     * @param $subject
     * @param $result
     * @param $schema
     * @param string $source
     * @param null $contextValue
     * @param array|null $variableValues
     * @param string|null $operationName
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterProcess(
        /*QueryProcessor*/ $subject,
        $result,
        /*Schema*/ $schema,
        string $source,
        /*ContextInterface*/ $contextValue = null,
        array $variableValues = null,
        string $operationName = null
    ) : array {
        if (!$this->config->isEnabled() || !$this->config->isConvertImagesInGraphQLRequests() || !$this->config->isBrowserWebPCompatible()) {
            return $result;
        }

        return $this->replaceLinks($result);
    }

    /**
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function replaceLinks($result) : array
    {
        if (is_array($result)) {

            $convertImagesInsertedViaPageBuilder = $this->config->convertImagesInsertedViaPageBuilder();

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

                    if ($convertImagesInsertedViaPageBuilder) {
                        $value = $this->pageBuilderParser->processDataBackgroundImages($value);
                        if (strpos((string)$value, '&lt;img')) {
                            $value = $this->processEncodedImage($value);
                        }
                    }
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }
    
     /**
      * @param $value
      * @return array|mixed|string|string[]
      */
    private function processEncodedImage($value)
    {
        $regex = "/&lt;img\s[^>]*?src\s*=\s*['\"]([^'\"]*?)['\"][^>]*?&gt;/mi";

        if (preg_match_all($regex, $value, $matches, PREG_OFFSET_CAPTURE) !== false) {

            $position = 0;
            foreach ($matches[0] as $i => $match) {
                $offset = $match[1] + $position;
                $htmlTag = $matches[0][$i][0];
                $originalHtmlTag = $htmlTag;
                if (0 !== stripos($htmlTag, '&lt;img')) {
                    continue;
                }
                $htmlTag = str_replace('&lt;', '<', $htmlTag);
                $htmlTag = str_replace('&gt;', '>', $htmlTag);

                $imageUrl = $matches[1][$i][0];
                $newHtmlTag = $this->htmlParser->getNewHtmlTag($imageUrl, $htmlTag);
                if (!$newHtmlTag) {
                    continue;
                }
                $newHtmlTag = str_replace('<', '&lt;', $newHtmlTag);
                $newHtmlTag = str_replace('>', '&gt;', $newHtmlTag);
                $value = substr_replace($value, $newHtmlTag, $offset, strlen($originalHtmlTag));
                $position = $position + (strlen($newHtmlTag) - strlen($originalHtmlTag));
            }
        }

        return $value;
    }
}
