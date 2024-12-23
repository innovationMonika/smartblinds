<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Instagram Feed for Magento 2
*/

namespace Amasty\InstagramFeed\Ui\DataProvider\Form;

use Amasty\InstagramFeed\Api\Data\PostInterface;
use Amasty\InstagramFeed\Api\PostRepositoryInterface;
use Amasty\InstagramFeed\Model\RegistryConstants;
use Amasty\InstagramFeed\Model\ResourceModel\Post\Collection;
use Amasty\InstagramFeed\Model\ResourceModel\Post\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Request\DataPersistorInterface;

class PostDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var PostRepositoryInterface
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        PostRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();
        if ($data['totalRecords'] > 0) {
            if (isset($data['items'][0][PostInterface::POST_ID])) {
                $postId = (int)$data['items'][0][PostInterface::POST_ID];
                $data = $this->repository->getById($postId)->getData();
                $data[$postId] = $this->addProductData($data);
            }
        }

        if ($savedData = $this->dataPersistor->get(RegistryConstants::POST_DATA)) {
            $savedPostId = isset($savedData[PostInterface::ID]) ? $savedData[PostInterface::ID] : null;
            if (isset($data[$savedPostId])) {
                $data[$savedPostId] = array_merge($data[$savedPostId], $savedData);
            } else {
                $data[$savedPostId] = $savedData;
            }
            $this->dataPersistor->clear(RegistryConstants::POST_DATA);
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function addProductData($data)
    {
        if (isset($data[PostInterface::PRODUCT_ID])) {
            $data['product_ids'] = [
                'post_product_container' => array_values($this->getProductsData($data[PostInterface::PRODUCT_ID]))
            ];
        }

        return $data;
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getProductsData($productIds)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($productIds)
            ->addAttributeToSelect(['status', 'thumbnail', 'name', 'price'], 'left');

        $result = [];
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($productCollection->getItems() as $product) {
            $result[$product->getId()] = $this->fillData($product);
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return array
     */
    private function fillData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return [
            'entity_id' => $product->getId(),
            'thumbnail' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
            'name'      => $product->getName(),
            'status'    => $product->getStatus(),
            'type_id'   => $product->getTypeId(),
            'sku'       => $product->getSku(),
            'price'     => $product->getPrice() ? $product->getPrice() : '-'
        ];
    }
}
