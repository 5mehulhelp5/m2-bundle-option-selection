/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'domReady',
    'Infrangible_BundleOptionSelection/js/model/selection',
    'priceUtils',
    'underscore'
], function ($, domReady, selection, utils, _) {
    'use strict';

    var globalOptions = {
        index: {},
        productBundleSelector: 'input.bundle.option, select.bundle.option, textarea.bundle.option',
        productBundleTriggerSelector: '.bundle-options-container',
        productBundleContainerSelector: null
    };

    $.widget('infrangible.bundleOptionSelection', {
        options: globalOptions,

        _create: function() {
        },

        _init: function() {
            var self = this;

            domReady(function() {
                var form = self.productBundleContainerSelector ? self.productBundleContainerSelector : self.element;
                var options = $(self.options.productBundleSelector, form);

                selection.setOptions(self.options);

                options.on('change', function() {
                    var selectedProductIds = self.collectSelectedProductIds();
                    console.debug('Bundle options have selected product ids: ' + _.compact(selectedProductIds));

                    $(self.options.productBundleTriggerSelector).trigger('bundle_changed', [selectedProductIds]);

                    var option = $(this);
                    var optionId = utils.findOptionId(option[0]);
                    var optionSelectedProductIds = selectedProductIds[optionId];
                    console.debug('Changed bundle option with id: ' +  optionId +
                        ' has selected product ids: ' + _.compact(optionSelectedProductIds));

                    $(self.options.productBundleTriggerSelector).trigger('bundle_option_changed',
                        [optionId, optionSelectedProductIds]);
                });

                var selectedProductIds = selection.collectSelectedProductIds(form);
                console.debug('Bundle options have selected product ids: ' + _.compact(selectedProductIds));

                $.each(selectedProductIds, function(optionId, optionSelectedProductIds) {
                    console.debug('Bundle option with id: ' +  optionId +
                        ' has selected product ids: ' + _.compact(optionSelectedProductIds));

                    $(self.options.productBundleTriggerSelector).trigger('bundle_option_changed',
                        [optionId, optionSelectedProductIds]);
                });

                $(self.options.productBundleTriggerSelector).trigger('bundle_changed', [selectedProductIds]);
            });
        },

        collectSelectedProductIds: function() {
            return selection.collectSelectedProductIds(
                this.productBundleContainerSelector ? this.productBundleContainerSelector : this.element);
        }
    });

    return $.infrangible.bundleOptionSelection;
});
