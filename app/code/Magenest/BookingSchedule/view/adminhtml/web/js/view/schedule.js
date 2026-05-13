/**
 * app/code/Magenest/BookingSchedule/view/adminhtml/web/js/view/schedule.js
 *
 * KnockoutJS ViewModel for the admin Booking Schedule grid.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function (Component, $, ko, $t, alert) {
    'use strict';

    var DAY_NAMES = [
        $t('Monday'), $t('Tuesday'), $t('Wednesday'),
        $t('Thursday'), $t('Friday'), $t('Saturday'), $t('Sunday')
    ];

    return Component.extend({
        defaults: {
            urls: {},
            formKey: '',
            initialWeek: '',
            visibleRows: 12,

            // Observable backing fields (converted by observe() below)
            weekStart: '',
            visibleStart: 13,    // 13 * 30min = 06:30
            cells: {},
            copyCount: 1,
            isLoading: false,
            isSaving: false,
            isCopying: false
        },

        initialize: function () {
            this._super();

            if (this.initialWeek) {
                this.weekStart(this.initialWeek);
            }
            this.loadWeek();
            return this;
        },

        initObservable: function () {
            // Must run before computed declarations: ko.computed evaluates immediately
            // and the computeds below read this.allSlots.
            this.allSlots = this._buildAllSlots();
            this.dirty = {};

            this._super().observe([
                'weekStart',
                'visibleStart',
                'cells',
                'copyCount',
                'isLoading',
                'isSaving',
                'isCopying'
            ]);

            this.weekDays      = ko.computed(this._computeWeekDays, this);
            this.visibleSlots  = ko.computed(this._computeVisibleSlots, this);
            this.weekRangeLabel = ko.computed(this._computeRangeLabel, this);
            this.canScrollUp   = ko.computed(function () {
                return this.visibleStart() > 0;
            }, this);
            this.canScrollDown = ko.computed(function () {
                return this.visibleStart() + this.visibleRows < this.allSlots.length;
            }, this);

            this.saveLabel = ko.computed(function () {
                return this.isSaving() ? $t('Saving...') : $t('Save');
            }, this);
            this.copyLabel = ko.computed(function () {
                return this.isCopying() ? $t('Copying...') : $t('Copy Week Assignment');
            }, this);

            return this;
        },

        // -- slot enumeration -----------------------------------------------

        _buildAllSlots: function () {
            var slots = [];
            for (var h = 0; h < 24; h++) {
                for (var m = 0; m < 60; m += 30) {
                    slots.push(this._pad(h) + ':' + this._pad(m));
                }
            }
            return slots;
        },

        _computeWeekDays: function () {
            var start = this.weekStart();
            if (!start) {
                return [];
            }
            var base = this._parseDate(start),
                days = [],
                i, d;

            for (i = 0; i < 7; i++) {
                d = new Date(base.getTime());
                d.setDate(d.getDate() + i);
                days.push({
                    isoDate: this._formatDate(d),
                    dayName: DAY_NAMES[i],
                    dayLabel: this._pad(d.getDate()) + '/' + this._pad(d.getMonth() + 1)
                });
            }
            return days;
        },

        _computeVisibleSlots: function () {
            var start = parseInt(this.visibleStart(), 10) || 0;
            return this.allSlots.slice(start, start + this.visibleRows);
        },

        _computeRangeLabel: function () {
            var days = this.weekDays();
            if (!days.length) {
                return '';
            }
            return days[0].dayLabel + ' - ' + days[6].dayLabel;
        },

        // -- cell accessors -------------------------------------------------

        getCell: function (date, time) {
            var key = date + '|' + time,
                map = this.cells();
            return map[key] || {stock: 0, used: 0, reservation: 0};
        },

        cellStock:       function (date, time) { return this.getCell(date, time).stock; },
        cellUsed:        function (date, time) { return this.getCell(date, time).used; },
        cellReservation: function (date, time) { return this.getCell(date, time).reservation; },

        // Pre-built label strings so the KO template doesn't need $t()
        labelReservation: function (date, time) {
            return this.cellReservation(date, time) + ' ' + $t('reservation');
        },
        labelUsed: function (date, time) {
            return this.cellUsed(date, time) + ' ' + $t('used');
        },

        isCellActive: function (date, time) {
            return this.cellStock(date, time) > 0;
        },

        updateStock: function (date, time, rawValue) {
            var stock = parseInt(rawValue, 10);
            if (isNaN(stock) || stock < 0) {
                stock = 0;
            }
            var key = date + '|' + time,
                map = $.extend({}, this.cells()),
                cell = $.extend({stock: 0, used: 0, reservation: 0}, map[key] || {});

            cell.stock = stock;
            map[key] = cell;
            this.cells(map);
            this.dirty[key] = true;
        },

        // KO `event: { input: ... }` callback factory
        onStockInput: function (date, time) {
            var self = this;
            return function (data, event) {
                self.updateStock(date, time, event.target.value);
            };
        },

        // -- navigation -----------------------------------------------------

        previousWeek: function () {
            this.weekStart(this._shiftWeek(-1));
            this.loadWeek();
        },

        nextWeek: function () {
            this.weekStart(this._shiftWeek(1));
            this.loadWeek();
        },

        scrollUp: function () {
            var v = this.visibleStart();
            if (v > 0) {
                this.visibleStart(v - 1);
            }
        },

        scrollDown: function () {
            var v = this.visibleStart();
            if (v + this.visibleRows < this.allSlots.length) {
                this.visibleStart(v + 1);
            }
        },

        // -- AJAX -----------------------------------------------------------

        loadWeek: function () {
            var self = this;
            if (self.isLoading()) {
                return;
            }
            self.isLoading(true);
            $('body').trigger('processStart');

            $.ajax({
                url: self.urls.getWeek,
                method: 'GET',
                dataType: 'json',
                data: {
                    week_start: self.weekStart(),
                    form_key: self.formKey
                }
            }).done(function (resp) {
                if (resp && resp.success) {
                    self.weekStart(resp.week_start);
                    self.cells(resp.slots || {});
                    self.dirty = {};
                } else {
                    alert({content: (resp && resp.message) || $t('Unable to load week.')});
                }
            }).fail(function () {
                alert({content: $t('Network error while loading week.')});
            }).always(function () {
                self.isLoading(false);
                $('body').trigger('processStop');
            });
        },

        save: function () {
            var self = this,
                slots = [],
                map = self.cells();

            Object.keys(self.dirty).forEach(function (key) {
                var parts = key.split('|'),
                    cell = map[key] || {stock: 0};
                slots.push({date: parts[0], time: parts[1], stock: cell.stock});
            });

            if (!slots.length) {
                alert({content: $t('No changes to save.')});
                return;
            }

            self.isSaving(true);
            $('body').trigger('processStart');

            $.ajax({
                url: self.urls.save,
                method: 'POST',
                data: {
                    form_key: self.formKey,
                    slots: slots
                }
            }).done(function (resp) {
                if (resp && resp.success) {
                    self.dirty = {};
                    alert({content: resp.message || $t('Saved.')});
                } else {
                    alert({content: (resp && resp.message) || $t('Save failed.')});
                }
            }).fail(function () {
                alert({content: $t('Network error while saving.')});
            }).always(function () {
                self.isSaving(false);
                $('body').trigger('processStop');
            });
        },

        copyWeek: function () {
            var self = this,
                copies = parseInt(self.copyCount(), 10);

            if (isNaN(copies) || copies < 1) {
                alert({content: $t('Enter a positive number of weeks to copy into.')});
                return;
            }

            self.isCopying(true);
            $('body').trigger('processStart');

            $.ajax({
                url: self.urls.copyWeek,
                method: 'POST',
                data: {
                    form_key: self.formKey,
                    week_start: self.weekStart(),
                    copies: copies
                }
            }).done(function (resp) {
                alert({content: (resp && resp.message) || (resp && resp.success ? $t('Copy done.') : $t('Copy failed.'))});
            }).fail(function () {
                alert({content: $t('Network error while copying.')});
            }).always(function () {
                self.isCopying(false);
                $('body').trigger('processStop');
            });
        },

        // -- date helpers ---------------------------------------------------

        _pad: function (n) {
            return (n < 10 ? '0' : '') + n;
        },

        _parseDate: function (str) {
            var parts = str.split('-');
            return new Date(
                parseInt(parts[0], 10),
                parseInt(parts[1], 10) - 1,
                parseInt(parts[2], 10)
            );
        },

        _formatDate: function (d) {
            return d.getFullYear() + '-' + this._pad(d.getMonth() + 1) + '-' + this._pad(d.getDate());
        },

        _shiftWeek: function (offsetWeeks) {
            var d = this._parseDate(this.weekStart());
            d.setDate(d.getDate() + 7 * offsetWeeks);
            return this._formatDate(d);
        }
    });
});
