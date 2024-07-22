<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider\UrlBuilder;

use GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\Collector\CollectorInterface;
use Magento\Backend\Model\UrlInterface;

class UrlBuilder
{
    private $urlBuilder;
    private $paramsCollector;

    public function __construct(
        UrlInterface $urlBuilder,
        CollectorInterface $paramsCollector
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->paramsCollector = $paramsCollector;
    }

    public function getUrl(string $urlPath, array $urlParams): string
    {
        return $this->urlBuilder->getUrl($urlPath, $this->paramsCollector->collect($urlParams));
    }
}
