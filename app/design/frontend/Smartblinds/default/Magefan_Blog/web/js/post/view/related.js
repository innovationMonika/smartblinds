define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko'
], function(
    $,
    _,
    Component,
    ko
) {
    'use strict';

    const
        postsPerChunk = 3,
        initialChunksCount = 1

    return Component.extend({
        defaults: {
            template: 'Magefan_Blog/post/view/related',
        },

        initialize: function () {
            this._super();

            var chunkedPosts = chunk(this.posts, postsPerChunk),
                stepChunks = chunk(chunkedPosts, initialChunksCount),
                shifted = stepChunks.shift(),
                chunks = _.isUndefined(shifted) ? [] : shifted;

            this.chunks(chunks);
            this.steps(stepChunks);

            return this;
        },

        showMore: function () {
            var steps = this.steps(),
                shifted = steps.shift();

            if (!_.isUndefined(shifted)) {
                this.chunks(this.chunks().concat(shifted));
            }

            this.steps(steps);

        },

        initObservable: function () {
            this._super()
                .observe(['chunks', 'steps'].join(' '));
            return this;
        }
    });

    function chunk(arr, n) {
        var chunks = [];
        while (arr.length > n) {
            chunks.push(arr.slice(0, n));
            arr = arr.slice(n, arr.length);
        }
        chunks.push(arr);
        return chunks;
    }
});
