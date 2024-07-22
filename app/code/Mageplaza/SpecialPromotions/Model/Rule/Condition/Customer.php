<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SpecialPromotions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SpecialPromotions\Model\Rule\Condition;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\Condition\Context;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Psr\Log\LoggerInterface;

/**
 * Class Customer
 * @package Mageplaza\SpecialPromotions\Model\Rule\Condition
 * @method getAttribute()
 * @method setAttribute($attribute)
 * @method array getAttributeOption()
 * @method setAttributeOption(array $attributes)
 */
class Customer extends AbstractProduct
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Customer constructor.
     *
     * @param Context $context
     * @param Data $backendData
     * @param Config $config
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Product $productResource
     * @param Collection $attrSetCollection
     * @param FormatInterface $localeFormat
     * @param CollectionFactory $collectionFactory
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendData,
        Config $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Product $productResource,
        Collection $attrSetCollection,
        FormatInterface $localeFormat,
        CollectionFactory $collectionFactory,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;

        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );

        $this->logger = $context->getLogger();
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $collection = $this->collectionFactory->create()->addExcludeHiddenFrontendFilter()->addSystemHiddenFilter();
        $attributes = [];

        /** @var \Magento\Customer\Model\Attribute $item */
        foreach ($collection->getItems() as $item) {
            $attributes[$item->getAttributeCode()] = $item->getDefaultFrontendLabel();
        }

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return Attribute|AbstractAttribute|DataObject
     */
    public function getAttributeObject()
    {
        try {
            $attribute = $this->_config->getAttribute(\Magento\Customer\Model\Customer::ENTITY, $this->getAttribute());

            if ($attribute && $attribute->getAttributeCode() === 'disable_auto_group_change') {
                $attribute->setSourceModel(Boolean::class);
            }

            return $attribute;
        } catch (LocalizedException $e) {
            $this->logger->critical($e);

            return new DataObject(['entity' => $this->_productFactory->create(), 'frontend_input' => 'text']);
        }
    }

    /**
     * @param AbstractModel $model
     *
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if ($model instanceof Address) {
            $model = $this->customerFactory->create()->load($model->getCustomerId());
        }
        if ($model instanceof \Magento\Customer\Model\Customer) {
            return parent::validate($model); // TODO: Change the autogenerated stub
        }

        return true;
    }
}
