define(['underscore'], function (_) {
    'use strict'

    return {
        calcMaxWidth: function (params) {
            var values = [];
            if (params.system.systemCategory !== 'venetian_blinds' && params.system.systemCategory !== 'honeycomb_blinds') {
                values = [
                    calcMaxWidthTorque(params),
                    calcMaxWidthBending(params)
                ];
            }
            if (params.product.maxWidth !== null) {
                values.push(params.product.maxWidth);
            }

            if (params.system.maxWidth && params.system.maxWidth > 0) {
                values.push(params.system.maxWidth);
            }

            if (params.system.systemCategory !== 'venetian_blinds' && params.system.systemCategory !== 'honeycomb_blinds') {
                var cp = getCheckParams(params);
                if (!cp.railroad || (cp.railroad && cp.usableWidth < cp.inputWidth && cp.usableHeight < cp.inputHeight)) {
                    values.push(cp.usableWidth);
                }
            }

            var min = Math.floor(_.min(values));
            return min >= 0 ? min : 0;
        },

        calcMaxHeight: function (params) {
            var values = [];
            if (params.system.systemCategory !== 'venetian_blinds' && params.system.systemCategory !== 'honeycomb_blinds') {
                values = [
                    calcMaxHeightTorque(params),
                    calcMaxHeightBending(params),
                    calcMaxHeightDiameter(params),
                    calcMaxHeightRotations(params)
                ];
            }
            if (params.product.maxHeight !== null) {
                values.push(params.product.maxHeight);
            }

            if (params.system.maxHeight) {
                values.push(params.system.maxHeight);
            }

            if (params.system.systemCategory !== 'venetian_blinds' && params.system.systemCategory !== 'honeycomb_blinds') {
                var cp = getCheckParams(params);
                if (cp.railroad && cp.usableWidth < cp.inputWidth && cp.usableHeight !== cp.inputHeight) {
                    values.push(cp.usableHeight);
                }
            }

            var min = Math.floor(_.min(values));
            return min >= 0 ? min : 0;
        }
    }

    function getCheckParams(params) {
        return {
            usableWidth: parseInt(params.product.width) - 100,
            usableHeight:  parseInt(params.product.width) - 200,
            inputWidth: params.input.width,
            inputHeight: params.input.height,
            railroad: params.product.railroad
        };
    }

    function calcMaxWidthTorque(params) {
        var system = params.system,
            height = params.input.height;
        return (
            (101936799.1845 * system.torque / (params.product.weight*parseFloat(system.priceCoefficient) * height + 1000 * system.bottomBarWeight))
            /
            ((params.product.thickness + system.tubeDiameter / 2) / 1000)
        )
    }

    function calcMaxWidthBending(params) {
        var system = params.system;
        return Math.pow(
            (system.bending * system.tube384Ei)
            /
            (
                5 * _.reduce(
                    [
                        (params.product.weight / 100000000) * parseFloat(system.priceCoefficient) * params.input.height,
                        system.bottomBarWeight / 100000,
                        system.tubeWeight / 100000
                    ],
                    function (result, value) {
                        return result + value;
                    },
                    0
                )
            ),
            1 / 4
        );
    }

    function calcMaxHeightTorque(params) {
        var system = params.system,
            width = params.input.width,
            thickness = params.product.thickness;
        return (
            (
                ((101.9367991845 * system.torque) / ((thickness*parseFloat(system.priceCoefficient)  + system.tubeDiameter / 2) / 1000))
                -
                (0.001 * width * system.bottomBarWeight)
            ) / (0.000001 * params.product.weight * width)
        )
    }

    function calcMaxHeightBending(params) {
        var system = params.system,
            width = params.input.width;
        return (
            (
                (
                    (200000000 * system.tube384Ei * system.bending)
                    -
                    (10000 * Math.pow(width, 4))
                    *
                    (system.bottomBarWeight + system.tubeWeight)
                )
                /
                (params.product.weight * parseFloat(system.priceCoefficient) * Math.pow(width, 4))
            )
            /
            10
        )
    }

    function calcMaxHeightDiameter(params) {
        var system = params.system;
        return (
            (
                Math.PI * (
                    Math.pow(system.systemDiameter, 2) - Math.pow(system.tubeDiameter, 2)
                )
            ) / (4 * params.product.thickness * parseFloat(system.priceCoefficient))
        )
    }

    function calcMaxHeightRotations(params) {
        var system = params.system;
        return (
            Math.PI * 20 * (
                system.systemDiameter + params.product.thickness*(20-1)
            )
        )
    }
});
