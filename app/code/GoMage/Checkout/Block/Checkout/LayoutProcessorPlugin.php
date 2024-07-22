<?php
namespace GoMage\Checkout\Block\Checkout;

class LayoutProcessorPlugin
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['street'] = [
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Street Address'), // You can change main label from here
            'required' => false, //turn false if you can removed main label
            'dataScope' => 'shippingAddress.street',
            'provider' => 'checkoutProvider',
            'sortOrder' => 7, // You can set short order of street fields from other checkout fields
            'type' => 'group',
            'additionalClasses' => 'street',
            'children' => [
                [
                    'label' => __('House no.'),
                    'sortOrder' => 1,
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'placeholder' => __('House no.'),
                        'additionalClasses' => 'small-right-field required'
                    ],
                    'dataScope' => '1',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => false, 'required-entry-street[1]' => true, "min_text_length" => 1],
                ],
            /*    [
                    'label' => __('Apartment'),
                    'sortOrder' => 2,
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'placeholder' => __('Apartment'),
                        'additionalClasses' => 'small-field'
                    ],
                    'dataScope' => '2',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => false],
                ],*/
                [
                    'label' => __('Street'),
                    'sortOrder' => 2,
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                        'placeholder' => __('Street'),
                        'additionalClasses' => 'clear-full-width-field required'
                    ],
                    'dataScope' => '0',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => false, 'required-entry-street[0]' => true, "min_text_length" => 1],
                ],
            ]
        ];

        $this->findStreet($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']);

        $customAttributeCode = 'ca_password';
        $customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                // customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/password',
                'additionalClasses' => 'create-account-password',
                'tooltip' => [
                    'description' => '',
                ],
                'placeholder' => __('Password'),
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
            'label' => __('Password'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 18,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;

        $customAttributeCode = 'ca_password_confirmation';
        $customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                // customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/password',
                'additionalClasses' => 'create-account-password',
                'tooltip' => [
                    'description' => '',
                ],
                'placeholder' => __('Confirm password'),
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
            'label' => __('Confirm password'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 19,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;

        return $jsLayout;
    }

    protected function findStreet(&$arr){
        if(!empty($arr) && is_array($arr)) {
            foreach ($arr as &$item) {
                if (isset($item['form-fields']['children']['street']['children'])) {
                    if(isset($item['form-fields']['children']['street']['children'][0])) {
                        $item['form-fields']['children']['street']['children'][0] =
                            array_merge(
                                $item['form-fields']['children']['street']['children'][0],
                                [
                                    'label' => __('House no.'),
                                    'sortOrder'  => 1,
                                    'validation' => ['required-entry' => false, 'required-entry-street[0]' => true, "min_text_length" => 1],
                                ]
                            );
                    }

                    /*if(isset($item['form-fields']['children']['street']['children'][1])) {
                        $item['form-fields']['children']['street']['children'][1] =
                            array_merge(
                                $item['form-fields']['children']['street']['children'][1],
                                [
                                    'label' => __('Apartment'),
                                    'sortOrder'  => 2,
                                    'validation' => ['required-entry' => false],
                                ]
                            );
                    }*/

                    if(isset($item['form-fields']['children']['street']['children'][1])) {
                        $item['form-fields']['children']['street']['children'][1] =
                            array_merge(
                                $item['form-fields']['children']['street']['children'][1],
                                [
                                    'label' => __('Street'),
                                    'sortOrder'  => 3,
                                    'validation' => ['required-entry' => false, 'required-entry-street[1]' => true, "min_text_length" => 1],
                                ]
                            );
                    }
                } elseif (!empty($item)) {
                    $this->findStreet($item);
                }
            }
        }
    }
}
