define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'underscore',
    'moment'
], function (DynamicRows, _, moment) {
    'use strict';

    var DAY_NAMES = [
        'Sunday', 'Monday', 'Tuesday', 'Wednesday',
        'Thursday', 'Friday', 'Saturday'
    ];

    var ACCEPTED_DATE_FORMATS = [
        'YYYY-MM-DD',
        'YYYY-MM-DDTHH:mm:ss',
        'YYYY-MM-DD HH:mm:ss',
        'MM/DD/YYYY',
        'M/D/YYYY',
        'DD/MM/YYYY',
        'D/M/YYYY'
    ];

    function pad(n) {
        return (n < 10 ? '0' : '') + n;
    }

    /**
     * Parse any of the admin's accepted date formats into a local-midnight Date.
     * Falls back to moment's lenient parser. Returns null on failure.
     */
    function parseLocalDate(value) {
        if (!value) {
            return null;
        }
        if (value instanceof Date) {
            return isNaN(value.getTime()) ? null : value;
        }
        var str = String(value).trim();
        var m   = moment(str, ACCEPTED_DATE_FORMATS, true);
        if (!m.isValid()) {
            m = moment(str);
        }
        if (!m.isValid()) {
            return null;
        }
        return new Date(m.year(), m.month(), m.date());
    }

    return DynamicRows.extend({
        defaults: {
            daysBefore: null,
            eventDate: null,
            listens: {
                daysBefore: 'rebuildSchedule',
                eventDate:  'rebuildSchedule'
            }
        },

        /**
         * Recompute the schedule rows whenever days_before_event or event_date changes.
         * Preserves user-entered event_time / details_message across regenerations.
         */
        rebuildSchedule: function () {
            var days    = parseInt(this.daysBefore, 10);
            var endDate = parseLocalDate(this.eventDate);

            if (!days || days < 1 || !endDate) {
                return;
            }

            var current = this.recordData() || [];
            var priorByPos = {};
            _.each(current, function (row) {
                priorByPos[parseInt(row.position, 10)] = row;
            });

            var rows = [];
            for (var i = 0; i < days; i++) {
                var d = new Date(endDate.getTime());
                d.setDate(d.getDate() - (days - 1 - i));
                var iso = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
                var prior = priorByPos[i] || {};
                rows.push({
                    position: i,
                    day_of_week: DAY_NAMES[d.getDay()],
                    schedule_date: iso,
                    event_time: prior.event_time || '',
                    details_message: prior.details_message || ''
                });
            }

            this.recordData(rows);
            this.reload();
        }
    });
});
