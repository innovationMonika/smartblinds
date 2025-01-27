<?php declare(strict_types=1);

namespace GoMage\Ui\Control\ButtonProvider\UrlBuilder\Params\Collector;

use Magento\Framework\App\RequestInterface;

class Request implements CollectorInterface
{
    private RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function collect(array $params): array
    {
        $data = [];
        foreach ($params as $requestKey => $passKey) {
            if ($value = $this->request->getParam($requestKey)) {
                $data[$passKey] = $value;
            }
        }
        return $data;
    }
}
