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

namespace Mageplaza\SpecialPromotions\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Mageplaza\SpecialPromotions\Block\Adminhtml\ProductXActions as BlockXActions;
use Mageplaza\SpecialPromotions\Model\Rule;
use Mageplaza\SpecialPromotions\Model\RuleFactory;

/**
 * Class ProductXActions
 * @package Mageplaza\SpecialPromotions\Block\Adminhtml\Promo\Quote\Edit\Tab
 */
class ProductXActions extends Generic implements
    TabInterface
{
    /**
     * Core registry
     *
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var BlockXActions
     */
    protected $_ruleActions;

    /**
     * @var Yesno
     * @deprecated 100.1.0
     */
    protected $_sourceYesno;

    /**
     * @var string
     */
    protected $_nameInLayout = 'product_x_actions_apply_to';

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * ProductXActions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $sourceYesno
     * @param BlockXActions $ruleActions
     * @param Fieldset $rendererFieldset
     * @param array $data
     * @param RuleFactory|null $ruleFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $sourceYesno,
        BlockXActions $ruleActions,
        Fieldset $rendererFieldset,
        array $data = [],
        RuleFactory $ruleFactory = null
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions = $ruleActions;
        $this->_sourceYesno = $sourceYesno;
        $this->ruleFactory = $ruleFactory ?: ObjectManager::getInstance()
            ->get(RuleFactory::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Product X Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Product X Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return ProductXActions
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = ObjectManager::getInstance()
            ->create(Rule::class);

        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of actions tab to supplied form.
     *
     * @param Rule $model
     * @param string $fieldsetId
     * @param string $formName
     *
     * @return Form
     * @throws LocalizedException
     */
    protected function addTabToForm(
        $model,
        $fieldsetId = 'mp_product_x_actions_fieldset',
        $formName = 'sales_rule_form'
    ) {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->ruleFactory->create();
            $model->load($id);
            $model->getProductXActions()->setJsFormObject('sales_rule_formproduct_x_rule_actions_fieldset_' . $id);
        }
        $actionsFieldSetId = $model->getActionsFieldSetId($formName . 'product_x_');

        $newChildUrl = $this->getUrl(
            'sales_rule/promo_quote/newProductXActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('mp_product_x_rule_');

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $actionsFieldSetId
        );

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Apply the rule only to cart items matching the following conditions ' .
                    '(leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'mp_product_x_actions',
            'text',
            [
                'name' => 'mp_product_x_apply_to',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );

        $form->setValues($model->getData());
        $this->setActionFormName($model->getProductXActions(), $formName);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        return $form;
    }

    /**
     * Handles addition of form name to action and its actions.
     *
     * @param AbstractCondition $actions
     * @param string $formName
     *
     * @return void
     */
    private function setActionFormName(AbstractCondition $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getProductXActions() && is_array($actions->getProductXActions())) {
            foreach ($actions->getProductXActions() as $condition) {
                $this->setActionFormName($condition, $formName);
            }
        }
    }
}
