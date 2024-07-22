<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Grouped Options for Magento 2
*/

declare(strict_types=1);

namespace Amasty\GroupedOptionsRewrite\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;

class UrlAlias extends \Magento\Ui\Component\Form\Field
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    }

    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        $config['storeData'] = $this->getStoreData();
        $this->setData('config', $config);
    }

    private function getStoreData(): array
    {
        $storeManagerDataList = $this->storeManager->getStores(true);
        ksort($storeManagerDataList);
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
         if($value['name'] !== 'Admin'){
            $options[] = [
                'index' => $key,
                'label' => $value['name'],
                'value' => ''
            ];
         }
        }

        return $options;
    }
}
