/**
 * app/code/Magenest/AjaxCart/view/frontend/web/js/ajax-cart.js
 *
 * jQuery widget that replaces all full-page POST actions on the cart page with
 * non-reloading AJAX equivalents:
 *
 *  • Update Shopping Cart button  → AJAX qty update  (ajaxcart/cart/ajaxUpdate)
 *  • Remove item link             → AJAX delete       (ajaxcart/cart/ajaxDelete)
 *  • Clear Shopping Cart button   → confirmation + regular form submit
 *                                   (cart will be empty; reload is acceptable)
 *
 * After each successful AJAX call the widget:
 *  1. Updates item qty / unit-price / subtotal cells in-place.
 *  2. Updates the Order Summary totals text (Subtotal, Shipping, Grand Total, Tax).
 *  3. Reloads the "cart" customerData section so the mini-cart header stays in sync.
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'jquery-ui-modules/widget'
], function ($, customerData, $t) {
    'use strict';

    $.widget('magenest.ajaxCart', {

        options: {
            /** POST endpoint for qty updates (set via data-mage-init in form.phtml) */
            updateUrl: '',
            /** POST endpoint for item deletion (set via data-mage-init in form.phtml) */
            deleteUrl: '',

            /** Selectors — kept in options so themes can override via data-mage-init */
            cartTableSelector:    '#shopping-cart-table',
            cartSummarySelector:  '#cart-totals',
            messagesSelector:     '[data-placeholder="messages"]',
            qtyInputSelector:     '[data-role="cart-item-qty"]',
            updateBtnRole:        'update-cart',
            clearBtnRole:         'clear-cart',
            deleteSelector:       '[data-ajax-delete]',
        },

        /** @private */
        _create: function () {
            this._bindSubmitButtons();
            this._bindDeleteActions();
        },

        // ─── Event binding ────────────────────────────────────────────────────────

        /**
         * Intercept form submit via button clicks so we can distinguish
         * "update_qty" from "empty_cart" without relying on :focus (unreliable
         * across browsers after async ops).
         * @private
         */
        _bindSubmitButtons: function () {
            var self = this;

            // Prevent the native form submission in all cases
            this.element.on('submit.ajaxcart', function (e) {
                e.preventDefault();
            });

            // Update button → AJAX update
            this.element.on('click.ajaxcart', '[data-role="' + this.options.updateBtnRole + '"]', function (e) {
                e.preventDefault();
                self._updateCart();
            });

            // Clear button → confirm then let regular form submit empty the cart
            this.element.on('click.ajaxcart', '[data-role="' + this.options.clearBtnRole + '"]', function (e) {
                e.preventDefault();
                self._confirmAndClearCart();
            });
        },

        /**
         * Intercept clicks on [data-ajax-delete] anchors rendered by our
         * overridden remove.phtml.  Using document-level delegation so dynamically
         * re-rendered rows are also covered (edge-case: pagination / re-render).
         * @private
         */
        _bindDeleteActions: function () {
            var self = this;

            $(document).on('click.ajaxcart', this.options.deleteSelector, function (e) {
                e.preventDefault();
                var itemId = parseInt($(this).data('ajax-delete'), 10);

                if (itemId) {
                    self._deleteItem(itemId);
                }
            });
        },

        // ─── Cart operations ──────────────────────────────────────────────────────

        /**
         * Collect current qty values and POST to ajaxcart/cart/ajaxUpdate.
         * @private
         */
        _updateCart: function () {
            var self    = this;
            var payload = this._buildCartPayload();

            this._startLoader();

            $.ajax({
                url:      this.options.updateUrl,
                type:     'POST',
                data:     payload,
                dataType: 'json'
            })
            .done(function (response) {
                if (response.success) {
                    self._applyCartUpdate(response);
                    self._showMessage('success', response.message);
                } else {
                    self._showMessage('error', response.message);
                }
            })
            .fail(function () {
                self._showMessage('error', $t('An error occurred. Please try again.'));
            })
            .always(function () {
                self._stopLoader();
            });
        },

        /**
         * POST item ID to ajaxcart/cart/ajaxDelete and remove the row from the DOM.
         * @param {number} itemId
         * @private
         */
        _deleteItem: function (itemId) {
            var self = this;

            this._startLoader();

            $.ajax({
                url:      this.options.deleteUrl,
                type:     'POST',
                data:     {
                    id:       itemId,
                    form_key: this._getFormKey()
                },
                dataType: 'json'
            })
            .done(function (response) {
                if (response.success) {
                    self._removeItemRow(itemId, function () {
                        if (response.is_empty) {
                            // Cart is now empty — reload so Magento renders
                            // the "Your cart is empty" block correctly
                            location.reload();
                            return;
                        }
                        self._applyCartUpdate(response);
                        self._showMessage('success', response.message);
                    });
                } else {
                    self._showMessage('error', response.message);
                }
            })
            .fail(function () {
                self._showMessage('error', $t('An error occurred. Please try again.'));
            })
            .always(function () {
                self._stopLoader();
            });
        },

        /**
         * Show a confirmation dialog then fall back to a regular form POST so
         * Magento's core UpdatePost controller handles the empty_cart action.
         * A page reload after clearing the cart is intentional and expected
         * (the empty-cart "no-items" template needs to render).
         * @private
         */
        _confirmAndClearCart: function () {
            if (!window.confirm($t('Are you sure you would like to remove all items from the shopping cart?'))) {
                return;
            }

            // Append the action flag the core UpdatePost controller expects.
            // We can't rely on the clicked button's [name=update_cart_action]
            // because we're submitting programmatically, not via click.
            $('<input>', {
                type:  'hidden',
                name:  'update_cart_action',
                value: 'empty_cart'
            }).appendTo(this.element);

            // Detach our submit/click interceptors and submit via native DOM API.
            // Native .submit() bypasses jQuery's submit-event handlers entirely
            // (avoids the recursion that .trigger('click') would cause on our
            //  own delegated click handler).
            this.element.off('submit.ajaxcart click.ajaxcart');
            this.element.get(0).submit();
        },

        // ─── DOM updates ──────────────────────────────────────────────────────────

        /**
         * Apply a full cart update response: refresh item cells + summary totals
         * + mini-cart section.
         * @param {{items: Array, totals: Object}} response
         * @private
         */
        _applyCartUpdate: function (response) {
            if (response.items && response.items.length) {
                this._updateItemRows(response.items);
            }

            if (response.totals) {
                this._updateSummaryTotals(response.totals);
            }

            // PERF: Reload only the cart section (mini-cart) — not the full page
            customerData.reload(['cart'], false);
        },

        /**
         * Update qty input, unit price cell, and subtotal cell for each item.
         * Uses input[name] as the stable anchor; avoids adding extra data-* to
         * core templates.
         * @param {Array} items
         * @private
         */
        _updateItemRows: function (items) {
            var tableSelector = this.options.cartTableSelector;

            $.each(items, function (idx, item) {
                var $input = $(tableSelector)
                    .find('input[name="cart[' + item.item_id + '][qty]"]');

                if (!$input.length) {
                    return; // item row not in DOM (e.g. another page of paginated cart)
                }

                var $tbody = $input.closest('tbody.cart.item');

                // Sync qty field with the value Magento actually accepted (suggestItemsQty)
                $input
                    .val(item.qty)
                    .attr('data-item-qty', item.qty);

                // Update displayed unit price (first .price span in the price cell)
                $tbody.find('.col.price .price').first().text(item.price);

                // Update displayed row subtotal
                $tbody.find('.col.subtotal .price').first().text(item.subtotal);
            });
        },

        /**
         * Update the Order Summary section totals.
         *
         * The summary totals are rendered by KnockoutJS UI components that read
         * from window.checkoutConfig.  We update the visible text nodes directly;
         * KO will not overwrite them unless its observables change (which they
         * won't — we only mutate the PHP-side quote, not the JS model).
         *
         * Supported totals codes: subtotal, grand_total, shipping, tax,
         * grand_total_incl_tax, grand_total_excl_tax.
         *
         * @param {Object} totals  Keyed by Magento total code.
         * @private
         */
        _updateSummaryTotals: function (totals) {
            var $summary = $(this.options.cartSummarySelector);

            if (!$summary.length) {
                return;
            }

            var map = {
                subtotal:             '.totals.sub .price',
                grand_total:          '.grand.totals:not(.excl):not(.incl) .price',
                grand_total_excl_tax: '.grand.totals.excl .price',
                grand_total_incl_tax: '.grand.totals.incl .price',
                shipping:             '.totals.shipping.excl .price, .totals.shipping:not(.incl) .price',
                tax:                  '.totals-tax .price'
            };

            $.each(map, function (code, selector) {
                if (totals[code] && totals[code].formatted) {
                    $summary.find(selector).text(totals[code].formatted);
                }
            });
        },

        /**
         * Fade out and remove the <tbody> row for a deleted item, then run
         * an optional callback.
         * @param {number}   itemId
         * @param {Function} [callback]
         * @private
         */
        _removeItemRow: function (itemId, callback) {
            var $tbody = $(this.options.cartTableSelector)
                .find('input[name="cart[' + itemId + '][qty]"]')
                .closest('tbody.cart.item');

            if ($tbody.length) {
                $tbody.fadeOut(300, function () {
                    $(this).remove();

                    if (typeof callback === 'function') {
                        callback();
                    }
                });
            } else if (typeof callback === 'function') {
                callback();
            }
        },

        // ─── Loader / messages ────────────────────────────────────────────────────

        /**
         * Show Magento's built-in full-page process loader.
         * @private
         */
        _startLoader: function () {
            $('body').trigger('processStart');
        },

        /**
         * Hide Magento's built-in full-page process loader.
         * @private
         */
        _stopLoader: function () {
            $('body').trigger('processStop');
        },

        /**
         * Render a success or error message in the page messages placeholder.
         * SEC: Escapes message text via DOM text node to prevent XSS.
         * @param {'success'|'error'} type
         * @param {string}            message
         * @private
         */
        _showMessage: function (type, message) {
            // SEC: Escape user-visible message via jQuery text() — no raw HTML
            var $msg  = $('<div>').text(String(message));
            var $wrap = $('<div class="messages">')
                .append(
                    $('<div class="message-' + type + ' ' + type + ' message">')
                        .append($('<div>').append($msg))
                );

            $(this.options.messagesSelector).html($wrap);

            // Scroll to the messages area smoothly
            var $target = $(this.options.messagesSelector);

            if ($target.length) {
                $('html, body').animate({ scrollTop: $target.offset().top - 20 }, 300);
            }
        },

        // ─── Helpers ──────────────────────────────────────────────────────────────

        /**
         * Build the cart POST payload from the current qty input values.
         * Reads form_key from the hidden input (most reliable source).
         * @returns {Object}
         * @private
         */
        _buildCartPayload: function () {
            var cart = {};

            this.element.find(this.options.qtyInputSelector).each(function () {
                // input name format: cart[ITEM_ID][qty]
                var matches = $(this).attr('name').match(/cart\[(\d+)\]\[qty\]/);

                if (matches) {
                    cart[matches[1]] = { qty: $(this).val() };
                }
            });

            return {
                cart:     cart,
                form_key: this._getFormKey()
            };
        },

        /**
         * Read the form key from the hidden input rendered by Magento's formkey block.
         * Falls back to the cookie if the input is somehow absent.
         * SEC: form_key is validated server-side on every POST.
         * @returns {string}
         * @private
         */
        _getFormKey: function () {
            var fromInput = this.element.find('input[name="form_key"]').val();

            if (fromInput) {
                return fromInput;
            }

            return $.mage.cookies ? $.mage.cookies.get('form_key') : '';
        }
    });

    return $.magenest.ajaxCart;
});