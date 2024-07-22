var config = {
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Magento_Swatches/js/swatch-renderer-mixin': true
            }
        }
    },
    map: {
        '*': {
            getSwatchSelectedProductId: 'Magento_Swatches/js/action/get-selected-product-id'
        }
    }
};
