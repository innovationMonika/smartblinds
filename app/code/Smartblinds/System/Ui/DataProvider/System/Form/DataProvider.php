<?php
namespace Smartblinds\System\Ui\DataProvider\System\Form;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $item->getData();
            $this->loadedData[$item->getId()]['store_id'] = explode(',', $item->getStoreviews());
        }
        return $this->loadedData;
    }
}
