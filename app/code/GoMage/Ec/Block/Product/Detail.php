<?php

namespace GoMage\Ec\Block\Product;

use Magento\Framework\View\Element\Template;

class Detail extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Anowave\Ec\Helper\Attributes
     */
    protected $attributes;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Anowave\Ec\Helper\Data
     */
    protected $helper;

    /**
     * @var \Anowave\Ec\Helper\Json
     */
    protected $jsonHelper;

    /**
     * @param \Anowave\Ec\Helper\Attributes $attributes
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Anowave\Ec\Helper\Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Anowave\Ec\Helper\Attributes $attributes,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Anowave\Ec\Helper\Data $helper,
        \Anowave\Ec\Helper\Json $jsonHelper,
        Template\Context $context,
        array $data = [])
    {

        $this->attributes = $attributes;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
        $this->eventManager = $context->getEventManager();

        parent::__construct($context, $data);
    }

    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDetailPush()
    {
        if ($info = $this->getLayout()->getBlock('product.info')) {
            $category = $this->registry->registry('current_category');
            if (!$category) {
                /**
                 * Filter current categories only
                 */
                $categories = $this->helper->getCurrentStoreProductCategories($info->getProduct());

                /**
                 * Cases when product does not exist in any category
                 */
                if (!$categories) {
                    $categories[] = $this->helper->getStoreRootDefaultCategoryId();
                }

                /**
                 * Load last category
                 */
                $category = $this->categoryRepository->get
                (
                    end($categories)
                );
            }
            /**
             * Create transport object
             *
             * @var \Magento\Framework\DataObject $transport
             */
            $transport = new \Magento\Framework\DataObject
            (
                [
                    'attributes' => $this->attributes->getAttributes(),
                    'product'    => $info->getProduct()
                ]
            );
            /**
             * Notify others
             */
            $this->eventManager->dispatch('ec_get_detail_attributes', ['transport' => $transport]);

            /**
             * Get response
             */
            $attributes = $transport->getAttributes();

            $data = $this->jsonHelper->encode([
                'event' => 'product-detailSend',
                'ecommerce' => [
                    'currencyCode' => $info->getProduct()->getStore()->getCurrentCurrencyCode(),
                    'detail'    => [
                        'products' => [
                            array_merge(
                                [
                                    'id'       => $info->getProduct()->getSku(),
                                    'name'     => $info->getProduct()->getName(),
                                    'price'    => $this->helper->getPrice($info->getProduct()),
                                    'brand'    => $this->helper->getBrand($info->getProduct()),
                                    'category' => $this->helper->getCategory($category),
                                    $this->helper->getStockDimensionIndex(true)
                                               => $this->helper->getStock($info->getProduct()),
                                    'quantity' => 1
                                ],
                                $attributes
                            )
                        ]
                    ]
                ]
            ]);
            return $data;
        }
        return false;
    }
}
