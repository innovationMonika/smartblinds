<?php

namespace GoMage\CatalogBlocks\Helper;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogData;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var BlockRepositoryInterface
     */
    private $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productReposytory;

    /**
     * @param Context $context
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param BlockRepositoryInterface $blockRepository
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productReposytory
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Data $catalogData,
        BlockRepositoryInterface $blockRepository,
        Configurable $configurable,
        ProductRepositoryInterface $productReposytory
    ) {
        parent::__construct($context);
        $this->catalogData = $catalogData;
        $this->blockRepository = $blockRepository;
        $this->configurable = $configurable;
        $this->productReposytory = $productReposytory;
    }

    /**
     * @param string $blockIdentifier
     * @return string|null
     */
    public function getAdditionalBlocksData($blockIdentifier = '')
    {
        $product = $this->catalogData->getProduct();
        if (!$product) {
            return null;
        }

        try {
            $block = $this->blockRepository->getById($product->getData($blockIdentifier));
        } catch (\Exception $e) {
            return null;
        }
        if ($block->getId()) {
            return $block->getIdentifier();
        }
        return null;
    }

    /**
     * @param string $blockIdentifier
     * @return string|null
     */
    public function getAdditionalCategoryBlocksData($blockIdentifier = '')
    {
        if ($category = $this->catalogData->getCategory()) {
            try {
                $block = $this->blockRepository->getById($category->getData($blockIdentifier));
            } catch (\Exception $e) {
                return null;
            }
            if ($block->getId()) {
                return $block->getIdentifier();
            }
        }
        return null;
    }

    /**
     * @param string $blockIdentifier
     * @return string|null
     */
    public function getConfigurableAdditionalBlocksData($blockIdentifier = '')
    {
        $product = $this->catalogData->getProduct();
        if (!$product) {
            return null;
        }

        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            $parent = $this->configurable->getParentIdsByChild($product->getId());
            if(isset($parent[0])){
                $product = $this->productReposytory->getById($parent[0]);
            }
        }

        try {
            $block = $this->blockRepository->getById($product->getData($blockIdentifier));
        } catch (\Exception $e) {
            return null;
        }
        if ($block->getId()) {
            return $block->getIdentifier();
        }
        return null;
    }

    /**
     * @param string $blockIdentifier
     * @return array|null
     */
    public function getFuncCategoryBlocksData($blockIdentifier = '')
    {
        $block= [];
        if ($category = $this->catalogData->getCategory()) {
            try {
                $categories = json_decode($category->getData($blockIdentifier), true);
                foreach ($categories as $cat) {
                    $block[] = $this->blockRepository->getById($cat['block'])->getIdentifier();
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        return $block;
    }

    /**
     * @param string $blockIdentifier
     * @return array|null
     */
    public function getFuncProductBlocksData($blockIdentifier = '')
    {
        $block= [];
        $product = $this->catalogData->getProduct();
        if (!$product) {
            return null;
        }

        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            $parent = $this->configurable->getParentIdsByChild($product->getId());
            if(isset($parent[0])){
                $product = $this->productReposytory->getById($parent[0]);
            }
        }
        try {
            $products = json_decode($product->getData($blockIdentifier), true);
            foreach ($products as $cat) {
                $block[] = $this->blockRepository->getById($cat['block'])->getIdentifier();
            }
        } catch (\Exception $e) {
            return null;
        }
        return $block;
    }
}
