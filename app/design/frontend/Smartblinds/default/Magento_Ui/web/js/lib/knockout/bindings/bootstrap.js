define([
    '../template/renderer',
    './resizable',
    './i18n',
    './scope',
    './range',
    './mage-init',
    './keyboard',
    './optgroup',
    './after-render',
    './autoselect',
    './datepicker',
    './outer_click',
    './fadeVisible',
    './collapsible',
    './staticChecked',
    './simple-checked',
    './bind-html',
    './tooltip',
    'knockoutjs/knockout-repeat',
    'knockoutjs/knockout-fast-foreach',
    './color-picker',
], function (
    renderer,
    resizable,
    i18n,
    scope,
    range,
    mageInit,
    keyboard,
    optgroup,
    afterRender,
    autoselect,
    datepicker,
    outerClick,
    fadeVisible,
    collapsible,
    staticChecked,
    simpleChecked,
    bindHtml,
    tooltip,
    repeat,
    fastForEach,
    colorPicker
) {
    'use strict';

    renderer.addAttribute('repeat', renderer.handlers.wrapAttribute);

    renderer.addAttribute('outerfasteach', {
        binding: 'fastForEach',
        handler: renderer.handlers.wrapAttribute
    });

    renderer
        .addNode('repeat')
        .addNode('fastForEach');

    return {
        resizable,
        i18n,
        scope,
        range,
        mageInit,
        keyboard,
        optgroup,
        afterRender,
        autoselect,
        datepicker,
        outerClick,
        fadeVisible,
        collapsible,
        staticChecked,
        simpleChecked,
        bindHtml,
        tooltip,
        repeat,
        fastForEach,
        colorPicker
    };
});
