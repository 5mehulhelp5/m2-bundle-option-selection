/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
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

    const globalOptions = {
        index: {},
        productBundleSelector: 'input.bundle.option, select.bundle.option, textarea.bundle.option',
        productBundleTriggerSelector: '.bundle-options-container',
        productBundleContainerSelector: null
    };

    // $.widget() returns the constructor, which is $.infrangible.bundleOptionSelection
    return $.widget('infrangible.bundleOptionSelection', {
        options: globalOptions,

        _create: function() {
        },

        _init: function() {
            const self = this;

            domReady(function() {
                const form = self.getContainer();
                const options = $(self.options.productBundleSelector, form);

                selection.setOptions(self.options);

                options.on('change', function() {
                    const selectedProductIds = self.collectSelectedProductIds();
                    console.debug('Bundle options have selected product ids: ' + _.compact(selectedProductIds));

                    $(self.options.productBundleTriggerSelector).trigger('bundle_changed', [selectedProductIds]);

                    const option = $(this);
                    const optionId = utils.findOptionId(option[0]);
                    const optionSelectedProductIds = selectedProductIds[optionId];
                    console.debug('Changed bundle option with id: ' +  optionId +
                        ' has selected product ids: ' + _.compact(optionSelectedProductIds));

                    $(self.options.productBundleTriggerSelector).trigger('bundle_option_changed',
                        [optionId, optionSelectedProductIds]);
                });

                const selectedProductIds = selection.collectSelectedProductIds(form);
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
            return selection.collectSelectedProductIds(this.getContainer());
        },

        getContainer: function() {
            return this.options.productBundleContainerSelector ?
                $(this.options.productBundleContainerSelector) : this.element;
        }
    });
});
