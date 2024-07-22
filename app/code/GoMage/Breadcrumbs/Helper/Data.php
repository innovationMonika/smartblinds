<?php

namespace GoMage\Breadcrumbs\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Config;

/**
 * Catalog data helper
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Data extends \Magento\Catalog\Helper\Data
{
    /**
     * Return current category path or get it from current category
     *
     * Creating array of categories|product paths for breadcrumbs
     *
     * @return array
     */
    public function getBreadcrumbPath()
    {
        if (!$this->_categoryPath) {
            $path = [];
            $category = $this->getCategory();
            $category = $this->getCategoryForBreadcrumb($category);

            if ($category) {
                $pathInStore = $category->getPathInStore();

                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                // add category path breadcrumb
                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path['category' . $categoryId] = [
                            'label' => $categories[$categoryId]->getName(),
                            'link' => $this->_isCategoryLink($categoryId) ? $categories[$categoryId]->getUrl() : ''
                        ];
                    }
                }
            }

            if ($this->getProduct()) {
                $path['product'] = ['label' => $this->getProduct()->getName()];
            }

            $this->_categoryPath = $path;
        }

        return $this->_categoryPath;
    }

    public function getCategoryForBreadcrumb($category){
        if (!$category) {
            $product = $this->getProduct();
            if($product) {
                $categoryIds = $product->getCategoryIds();
                if (empty($categoryIds) && $product->getTypeId() == 'configurable') {
                    $children = $product->getTypeInstance()->getChildrenIds($product->getId());
                    foreach ($children[0] as $child) {
                        $productChild = $this->productRepository->getById($child);
                        $categoryIds = $productChild->getCategoryIds();
                        if (!empty($categoryIds)) {
                            break;
                        }
                    }
                }
                if (!empty($categoryIds)) {
                    foreach ($categoryIds as $categoryId) {
                        $category = $this->categoryRepository->get($categoryId);
                        if ($category->getLevel() < 3) {
                            break;
                        }
                    }
                }
            }
        }

        return $category;
    }
}
