<?php declare(strict_types=1);

namespace Smartblinds\Shopby\Block\Product\ProductList;

class ChildrenCategoryList
    extends \Amasty\Shopby\Block\Product\ProductList\ChildrenCategoryList
{
    private $registry;
    private $categoryCollection;

    private $childrenCategories = [];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Shopby\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection,
        array $data = []
    ) {
        parent::__construct(
            $context, $coreRegistry, $categoryHelper, $categoryCollection, $data
        );
        $this->registry = $coreRegistry;
        $this->categoryCollection = $categoryCollection;
    }

    public function getChildrenCategories()
    {
        if (empty($this->childrenCategories)) {
            /**  @var \Magento\Catalog\Model\Category $currentCategory */
            $currentCategory = $this->registry->registry('current_category');
            $categories = $this->getSliderCategories($currentCategory);
            if ($currentCategory->getLevel() >= 3 && !$categories) {
                $categories = $this->getSliderCategories(
                    $currentCategory->getParentCategory(),
                    $currentCategory
                );
            }
            $this->childrenCategories = $categories;
        }

        return $this->childrenCategories;
    }

    private function getSliderCategories($currentCategory, $childCategory = null)
    {
        $categories = [];

        $collection = $currentCategory->getChildrenCategories();

        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Collection) {
            /**
             * @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection
             */
            $collection->setLoadProductCount(true);
            $collection->addNameToResult();
            $collection->addOrderField('name');
            $collection->addAttributeToSelect('image');
            $collection->addAttributeToSelect('thumbnail');
            $collection->addAttributeToFilter('is_active', ['eq' => true]);
            if ($this->getData('attributes_to_select')) {
                $collection->addAttributeToSelect($this->getData('attributes_to_select'));
            }
        } elseif (is_array($collection) && !empty($collection)) {
            $this->categoryCollection->loadProductCount($collection);
        }

        foreach ($collection as $category) {
            if ($childCategory && $category->getId() == $childCategory->getId()) {
                $category->setIsSliderSelected(true);
                $categories[] = $category;
                continue;
            }
            if ($category->getData('product_count') || $category->getProductCount()) {
                $categories[] = $category;
            }
        }

        return $categories;
    }
}
