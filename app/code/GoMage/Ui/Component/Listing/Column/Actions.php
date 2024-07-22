<?php declare(strict_types=1);

namespace GoMage\Ui\Component\Listing\Column;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    private UrlInterface $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $indexField = $this->getData('config/indexField') ?: 'entity_id';
            if (!isset($item[$indexField])) {
                continue;
            }
            $actionsConfig = $this->getData('config/actions') ?? [];
            $actions = [];
            foreach ($actionsConfig as $actionName => $actionConfig) {
                $actionUrl = $this->urlBuilder->getUrl(
                    $actionConfig['url_path'],
                    [$actionConfig['url_param'] => $item[$indexField]]
                );
                $action = [
                    'label' => $actionConfig['label'],
                    'href'  => $actionUrl
                ];
                $actions[$actionName] = $action;
            }
            $item[$this->getName()] = $actions;
        }
        return $dataSource;
    }
}
