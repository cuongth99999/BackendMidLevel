/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($, configurableVariationQty) {
    'use strict';

    return function (SwatchRenderer) {
        $.widget('mage.SwatchRenderer', SwatchRenderer, {

            /** @inheritdoc */
            _OnClick: function ($this, widget) {
                this._super($this, widget);

                // Change qty current child salable qty
                if (this.getProduct()) {
                    if (this.options.jsonConfig.childSalableQty[this.getProduct()] !== undefined) {
                        let childSalableQty = this.options.jsonConfig.childSalableQty[this.getProduct()];
                        let childSalableQtyText = childSalableQty > 0 ? childSalableQty + $.mage.__(' in stock') : $t('Out of stock');
                        $('#child-salable-qty').text(childSalableQtyText);
                    }
                }

            }
        });

        return $.mage.SwatchRenderer;
    };
});
