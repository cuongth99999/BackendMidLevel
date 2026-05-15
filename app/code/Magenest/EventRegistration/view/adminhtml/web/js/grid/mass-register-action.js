define([
    'uiCollection',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (Collection, $, ko, _, modal, $t) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Magenest_EventRegistration/grid/mass-register-modal',
            eventsUrl: '',
            schedulesUrl: '',
            submitUrl: '',
            events: [],
            schedules: [],
            selectedEventId: '',
            selectedScheduleId: '',
            note: '',
            loadingEvents: false,
            loadingSchedules: false,
            submitting: false,
            tracks: {
                events: true,
                schedules: true,
                selectedEventId: true,
                selectedScheduleId: true,
                note: true,
                loadingEvents: true,
                loadingSchedules: true,
                submitting: true
            },
            modalConfig: null,
            currentSelections: null
        },

        /**
         * Massaction callback target. Magento calls this with the action config
         * and the current grid selection payload.
         */
        openModal: function (action, selections) {
            this.currentSelections = selections || {};
            this._resetForm();
            this._loadEvents();
            this._ensureModal();
            this.modalConfig.openModal();
        },

        _resetForm: function () {
            this.selectedEventId = '';
            this.selectedScheduleId = '';
            this.schedules = [];
            this.note = '';
        },

        _loadEvents: function () {
            var self = this;
            this.loadingEvents = true;
            $.ajax({
                url: this.eventsUrl,
                type: 'GET',
                dataType: 'json',
                cache: false
            }).done(function (resp) {
                self.events = resp && resp.events ? resp.events : [];
            }).always(function () {
                self.loadingEvents = false;
            });
        },

        /**
         * Event handler bound by the modal's <select> on the event field.
         * Sub-loads the schedule list for the chosen event.
         */
        onEventChanged: function () {
            var eventId = parseInt(this.selectedEventId, 10);
            this.selectedScheduleId = '';
            this.schedules = [];
            if (!eventId) {
                return;
            }
            var self = this;
            this.loadingSchedules = true;
            $.ajax({
                url: this.schedulesUrl,
                type: 'GET',
                dataType: 'json',
                data: {event_id: eventId},
                cache: false
            }).done(function (resp) {
                self.schedules = resp && resp.schedules ? resp.schedules : [];
            }).always(function () {
                self.loadingSchedules = false;
            });
        },

        _ensureModal: function () {
            if (this.modalConfig) {
                return;
            }
            var self = this;
            // Each customer listing instance has at most one mass-register modal.
            var $el = $('.magenest-event-register-modal__body').first();
            if (!$el.length) {
                return;
            }
            this.modalConfig = modal({
                type: 'popup',
                modalClass: 'magenest-event-register-modal',
                title: $t('Register Customers to Event'),
                clickableOverlay: false,
                buttons: [
                    {
                        text: $t('Cancel'),
                        class: 'action-secondary',
                        click: function () { this.closeModal(); }
                    },
                    {
                        text: $t('Register'),
                        class: 'action-primary',
                        click: function () { self.submit(); }
                    }
                ]
            }, $el);
        },

        /**
         * POST event_id / schedule_id / note + grid selection payload.
         * Server publishes one queue message per 1000 customers.
         */
        submit: function () {
            if (this.submitting) {
                return;
            }
            if (!this.selectedEventId) {
                window.alert($t('Please choose an event.'));
                return;
            }
            if (!this.selectedScheduleId) {
                window.alert($t('Please choose an event time.'));
                return;
            }

            var sel  = this.currentSelections || {};
            var data = {
                form_key:    window.FORM_KEY,
                event_id:    this.selectedEventId,
                schedule_id: this.selectedScheduleId,
                note:        this.note || '',
                mode:        sel.excludeMode ? 'exclude' : 'include',
                selected:    (sel.selected && sel.selected.length) ? sel.selected : [],
                excluded:    (sel.excluded && sel.excluded.length) ? sel.excluded : []
            };
            if (sel.params && sel.params.filters) {
                data.filters = sel.params.filters;
            }
            if (sel.params && sel.params.search) {
                data.search = sel.params.search;
            }

            var self = this;
            this.submitting = true;
            $('body').trigger('processStart');
            $.ajax({
                url: this.submitUrl,
                type: 'POST',
                dataType: 'json',
                data: data
            }).done(function (resp) {
                if (resp && resp.success) {
                    self.modalConfig.closeModal();
                    window.alert(resp.message);
                } else {
                    window.alert((resp && resp.message) || $t('Unable to queue registrations.'));
                }
            }).fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : $t('Unable to queue registrations.');
                window.alert(msg);
            }).always(function () {
                self.submitting = false;
                $('body').trigger('processStop');
            });
        }
    });
});
