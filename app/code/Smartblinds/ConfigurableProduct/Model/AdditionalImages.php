<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository;

class AdditionalImages
{
    private $config;
    private RequestInterface $request;
    private Repository $assetRepository;

    private $urls;

    public function __construct(
        Config $config,
        RequestInterface $request,
        Repository $assetRepository
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->assetRepository = $assetRepository;
    }

    public function getUrls()
    {
        if (isset($this->urls)) {
            return $this->urls;
        }

        $paths = $this->config->getAdditionalImages();
        $urls = [];
        foreach ($paths as $path) {
            try {
                $urls[] = $this->assetRepository->getUrlWithParams($path, ['_secure' => $this->request->isSecure()]);
            } catch (LocalizedException $e) {
                continue;
            }
        }
        $this->urls = $urls;
        return $urls;
    }
}
