/**
 * Created by jake on 25/5/17.
 */

define(['oroui/js/mediator'], function(mediator) {
    'use strict';

    return {
        init: function(deferred, options) {
            options.gridPromise.done(function (grid) {
                grid.collection.on('beforeFetch', function() {
                    mediator.trigger('datagrid:doReset:{{attendance-contacts-grid}}');
                });
                deferred.resolve();
            });
        }
    };
});