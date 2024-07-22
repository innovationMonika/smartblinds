define([
    'jquery',
    'underscore',
    'sampleBasket',
    'getSwatchSelectedProductId'
], function(
    $,
    _,
    basket,
    getSwatchSelectedProductId
) {
    'use strict';

    var $window = $(window);

    var settings = {
        selectors: {
            swatchOptions: 'div[data-role="swatch-options"]',
            swatchInput: '.swatch-input',
            sampleClick: '.js-sample-click',
            sampleState: '.js-sample-state'
        }
    };

    var currentItemId = null;

    var $swatchOptions,
        $swatchInput,
        $click,
        $stateElement;

    function create () {
        $window.on('sample-basket-updated', updateStateIcon);
        $swatchOptions = $(settings.selectors.swatchOptions);
        if (!$swatchOptions.length) {
            return;
        }
        $swatchInput = $swatchOptions.find(settings.selectors.swatchInput);
        if (!$swatchInput.length) {
            $swatchOptions.on('swatch.initialized', init);
            return;
        }
        init();
    }

    function init() {
        $swatchInput = $swatchOptions.find(settings.selectors.swatchInput);
        $swatchInput.on('change', updateCurrentItemId);
        $click = $(settings.selectors.sampleClick);
        $click.on('click', updateBasket);
        $stateElement = $(settings.selectors.sampleState);
        updateCurrentItemId();
    }

    function updateCurrentItemId (e) {
        var productId = parseInt(getSwatchSelectedProductId());
        if (productId) {
            currentItemId = productId;
            updateStateIcon();
        }
    }

    function updateBasket () {
        var item = _.findWhere(settings.options.items, {id: currentItemId});
        if (!item) {
            return;
        }
        if (basket.getItemBySwatches(item)) {
            basket.removeItemBySwatches(item);
            return;
        }
        try {
            var parentItem = null;
            if (item.parentId) {
                parentItem = _.findWhere(settings.options.items, {id: item.parentId});
            }
            basket.addItem(item, parentItem);
        } catch (e) {
            alert(e);
        }
        updateStateIcon();
    }

    function updateStateIcon (e) {
        var item = _.findWhere(settings.options.items, {id: currentItemId});
        if (item && basket.getItemBySwatches(item)) {
            $stateElement.html('<span class="tick-icon">' + settings.options.addedToCartIcon + '</span>');
            $stateElement.parents(settings.selectors.sampleClick).addClass('active');
            return;
        }
        $stateElement.each(function() {
            var $this = $(this);
            var addToCartIconHtml = ''
            if (!$this.data('hide-add-button')) {
                addToCartIconHtml = settings.options.addToCartIcon;
            }
            $this.html(addToCartIconHtml);
            $this.parents(settings.selectors.sampleClick).removeClass('active');
        });
    }

    return function (options) {
        settings.options = options;
        create();
    }
});
