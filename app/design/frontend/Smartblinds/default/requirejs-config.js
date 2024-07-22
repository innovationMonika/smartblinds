var config = {

    config: {
        mixins: {
            'Magento_Search/js/form-mini': {
                'Magento_Search/js/form-mini-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information/address-renderer/default': {
                'Magento_Checkout/js/view/shipping-information/address-renderer/default-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Magento_Checkout/js/view/shipping-address/address-renderer/default-mixin': true
            }
        }
    },

    map: {
        '*': {
            'topbar': 'Magento_Theme/js/topbar',
            'homeCategories': 'Magento_Cms/js/home/categories',
            'categoriesSlider': 'Magento_Cms/js/categories-slider',
            'homeSlider': 'Magento_Cms/js/home/slider',
            'homeTabs': 'Magento_Cms/js/home/tabs',
            'homeHero': 'Magento_Cms/js/home/hero',
            'instagramSlider': 'Amasty_InstagramFeed/js/am-grid-slider',
            'pdpSlider': 'Magento_Catalog/js/pdp-slider',
            'blogSlider': 'Magefan_Blog/js/blog-slider'
        }
    },

    paths: {
        'swiper': 'js/plugins/swiper-bundle'
    }

};
