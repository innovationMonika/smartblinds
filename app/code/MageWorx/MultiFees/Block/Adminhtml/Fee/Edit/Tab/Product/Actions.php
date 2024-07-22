<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Product;

use MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Tab\Actions as OriginalActions;

/**
 * Fee actions form tab
 */
class Actions extends OriginalActions
{
   protected $controllerName = 'fee_product';

    /**
     * Prepare form before rendering HTML
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \MageWorx\MultiFees\Model\AbstractFee $model */
        $model = $this->_coreRegistry->registry('mageworx_multifees_fee');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('mageworx_multifees/' . $this->controllerName .
                          '/newActionHtml/form/rule_rule_actions_fieldset'
            )
        )->setNameInLayout('mageworx_multifees_catalog_rule_renderer');

        $fieldset = $form->addFieldset(
            'rule_actions_fieldset',
            [
                'legend' => __(
                    'Apply the rule only to cart items matching the following conditions (leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            ['name' => 'actions', 'label' => __('Apply to'), 'title' => __('Apply to')]
        )->setRule(
            $model
        )->setRenderer(
            $this->actions
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return $this;
    }
}
