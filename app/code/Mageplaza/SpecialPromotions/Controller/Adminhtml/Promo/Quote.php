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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Class Quote
 * @package Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo
 */
abstract class Quote extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var RuleResource
     */
    protected $ruleResource;

    /**
     * Quote constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param Date $dateFilter
     * @param CollectionFactory $ruleCollectionFactory
     * @param RuleFactory $ruleFactory
     * @param RuleResource $ruleResource
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Date $dateFilter,
        CollectionFactory $ruleCollectionFactory,
        RuleFactory $ruleFactory,
        RuleResource $ruleResource
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;

        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
    }
}
