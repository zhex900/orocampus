/*global define*/
define([
    'oro/datagrid/action/mass-action'
], function (MassAction) {
    'use strict';

    var AutoallocateAction;

    /**
     * @export  oro/datagrid/action/autoallocate-action
     * @class   oro.datagrid.action.AutoAllocateAction
     * @extends oro.datagrid.action.MassAction
     */
    AutoallocateAction = MassAction.extend({
        defaultMessages: {
            confirm_title: 'Mass Action Auto Allocate Confirmation',
            confirm_content: 'Are you sure you want to do this?',
            confirm_ok: 'Yes, do it',
            confirm_cancel: 'Cancel',
        }
    });

    return AutoallocateAction;
});