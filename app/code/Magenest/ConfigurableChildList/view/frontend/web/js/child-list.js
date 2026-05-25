/**
 * app/code/Magenest/ConfigurableChildList/view/frontend/web/js/child-list.js
 *
 * Filters and renders the per-child product cards in place of the last
 * configurable attribute, and adds the chosen child to cart via AJAX.
 */
define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'jquery-ui-modules/widget'
], function ($, _, $t, urlBuilder, customerData, alert) {
    'use strict';

    var HIDE_CLASS = 'magenest-child-list-hidden';
    var STYLE_ID = 'magenest-child-list-style';

    /**
     * Inject a single global stylesheet once so we can hide DOM that may
     * be rendered later (e.g. swatches widget initialisation).
     */
    function ensureHideStyle() {
        if (document.getElementById(STYLE_ID)) {
            return;
        }
        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.appendChild(document.createTextNode('.' + HIDE_CLASS + '{display:none !important;}'));
        document.head.appendChild(style);
    }

    $.widget('magenest.configurableChildList', {
        options: {
            productId: null,
            addToCartUrl: '',
            formKey: '',
            attributes: {},
            orderedAttrIds: [],
            lastAttributeId: null,
            children: [],
            i18n: {}
        },

        _create: function () {
            ensureHideStyle();
            this.$items = this.element.find('[data-role="items"]');
            this.$empty = this.element.find('[data-role="empty"]');
            this._hideLastAttribute();
            this._hideMainAddToCart();
            this._bindEvents();
            this._refresh();
        },

        _hideLastAttribute: function () {
            var lastId = parseInt(this.options.lastAttributeId);
            if (!lastId) {
                return;
            }
            var apply = function () {
                // Native dropdown wrapper.
                $('[name="super_attribute[' + lastId + ']"]')
                    .closest('.field.configurable')
                    .addClass(HIDE_CLASS);
                // Swatches wrapper. Note: data-attribute-id, not attribute-id.
                $('.swatch-attribute[data-attribute-id="' + lastId + '"]')
                    .addClass(HIDE_CLASS);
            };
            apply();
            // Swatches widget renders after this widget initialises — re-apply.
            setTimeout(apply, 200);
            setTimeout(apply, 800);
        },

        _hideMainAddToCart: function () {
            $('#product_addtocart_form .box-tocart').addClass(HIDE_CLASS);
        },

        _bindEvents: function () {
            var self = this;
            // `.super-attribute-select` is shared by both the native <select>
            // and the hidden <input> the swatch widget creates. Swatch clicks
            // already fire `change` on that hidden input, so one listener covers
            // both cases.
            $('#product_addtocart_form')
                .on('change.magenestChildList', '.super-attribute-select', function () {
                    self._refresh();
                });

            this.element.on('click', '[data-role="add-to-cart"]', function (e) {
                e.preventDefault();
                self._handleAdd($(this));
            });
        },

        _getSelectedNonLast: function () {
            var lastId = parseInt(this.options.lastAttributeId);
            var selected = {};

            // `.super-attribute-select` matches both <select> (native template)
            // and <input> (swatches template) — the value lives there in both cases.
            $('.super-attribute-select').each(function () {
                var name = $(this).attr('name') || '';
                var m = name.match(/\[(\d+)\]/);
                if (!m) {
                    return;
                }
                var attrId = parseInt(m[1]);
                if (attrId === lastId) {
                    return;
                }
                var val = $(this).val();
                if (val) {
                    selected[attrId] = parseInt(val);
                }
            });

            return selected;
        },

        _refresh: function () {
            var self = this;
            var selected = this._getSelectedNonLast();
            var lastId = parseInt(this.options.lastAttributeId);
            var nonLastIds = _.filter(this.options.orderedAttrIds, function (id) {
                return parseInt(id) !== lastId;
            });

            var allChosen = _.every(nonLastIds, function (id) {
                return selected.hasOwnProperty(id);
            });

            if (!allChosen) {
                this._renderEmpty(this.options.i18n.empty);
                return;
            }

            var matches = _.filter(this.options.children, function (child) {
                return _.every(selected, function (valueId, attrId) {
                    return parseInt(child.attributes[attrId]) === parseInt(valueId);
                });
            });

            if (matches.length === 0) {
                this._renderEmpty(this.options.i18n.noMatch);
                return;
            }

            this._renderItems(matches);
        },

        _renderEmpty: function (msg) {
            this.$items.empty();
            this.$empty.text(msg || '').show();
        },

        _renderItems: function (items) {
            var self = this;
            this.$empty.hide().text('');
            this.$items.empty();
            items.forEach(function (item) {
                self.$items.append(self._buildCard(item));
            });
        },

        _buildCard: function (item) {
            var i18n = this.options.i18n;
            var disabled = !item.in_stock;

            var $card = $('<div/>', {
                'class': 'magenest-child-card' + (disabled ? ' magenest-child-card--disabled' : ''),
                'data-child-id': item.id
            });

            // Media
            $('<div/>', {'class': 'magenest-child-card__media'}).append(
                $('<img/>', {src: item.image, alt: item.name})
            ).appendTo($card);

            // Info
            var $info = $('<div/>', {'class': 'magenest-child-card__info'}).appendTo($card);
            $('<span/>', {'class': 'magenest-child-card__name', text: item.name}).appendTo($info);
            $('<div/>', {
                'class': 'magenest-child-card__sku',
                text: i18n.sku + ': ' + item.sku
            }).appendTo($info);
            if (item.short_description) {
                $('<div/>', {
                    'class': 'magenest-child-card__desc',
                    text: item.short_description
                }).appendTo($info);
            }

            // Buy column
            var $buy = $('<div/>', {'class': 'magenest-child-card__buy'}).appendTo($card);
            // PriceCurrency::format(..., false) returns plain text; use text() to avoid XSS.
            $('<div/>', {'class': 'magenest-child-card__price', text: item.price}).appendTo($buy);

            var $actions = $('<div/>', {'class': 'magenest-child-card__actions'}).appendTo($buy);
            $('<input/>', {
                type: 'number',
                'class': 'input-text qty',
                'data-role': 'qty',
                value: 1,
                min: 1,
                step: 1,
                'aria-label': i18n.qty
            }).appendTo($actions);
            $('<button/>', {
                type: 'button',
                'class': 'action primary',
                'data-role': 'add-to-cart',
                disabled: disabled
            }).append(
                $('<span/>', {text: disabled ? i18n.outOfStock : i18n.addToCart})
            ).appendTo($actions);

            // Keep the data on the element so _handleAdd can read it back.
            $card.data('child', item);
            return $card;
        },

        _handleAdd: function ($btn) {
            var self = this;
            var $card = $btn.closest('.magenest-child-card');
            var child = $card.data('child');
            if (!child) {
                return;
            }

            var qty = parseInt($card.find('[data-role="qty"]').val(), 10);
            if (!qty || qty < 1) {
                qty = 1;
            }

            var formData = new FormData();
            formData.append('form_key', $('input[name="form_key"]').val() || self.options.formKey);
            formData.append('product', self.options.productId);
            formData.append('selected_configurable_option', child.id);
            formData.append('qty', qty);
            _.each(child.attributes, function (valueId, attrId) {
                formData.append('super_attribute[' + attrId + ']', valueId);
            });

            $('body').trigger('processStart');
            $btn.prop('disabled', true);

            $.ajax({
                url: self.options.addToCartUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).done(function () {
                customerData.reload(['cart'], false);
                self._notify(self.options.i18n.added);
            }).fail(function (xhr) {
                var msg = self.options.i18n.genericErr;
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp && resp.message) {
                        msg = resp.message;
                    }
                } catch (e) { /* ignore */ }
                alert({content: msg});
            }).always(function () {
                $('body').trigger('processStop');
                if (child.in_stock) {
                    $btn.prop('disabled', false);
                }
            });
        },

        _notify: function (msg) {
            var $msg = $('<div/>', {'class': 'message message-success success'})
                .append($('<div/>', {text: msg}))
                .hide();
            this.element.before($msg);
            $msg.fadeIn(150);
            setTimeout(function () {
                $msg.fadeOut(400, function () { $msg.remove(); });
            }, 2200);
        }
    });

    return $.magenest.configurableChildList;
});
