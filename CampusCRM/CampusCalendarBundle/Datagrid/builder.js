/**
 * Created by jake on 25/5/17.
 */

define(function(require) {
    'use strict';
    var mediator = require('oroui/js/mediator');
    return {
        init: function(deferred, options) {

            options.gridPromise.done(function(grid) {
                // add listener for first grid's collection
                grid.collection.on('beforeFetch', function() {
                    // make second grid refresh
                    mediator.trigger('datagrid:doRefresh:{{ campuscalendar-event-attendance-grid }}');
                });
                deferred.resolve();
            });
        }
    };
});