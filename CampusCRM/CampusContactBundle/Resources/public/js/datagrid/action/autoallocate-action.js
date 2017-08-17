/*global define*/
//CampusCRM/CampusContactBundle/Resources/public/js/datagrid/action/autoallocate-action.js
define([
    'oro/datagrid/action/mass-action'
], function (MassAction) {
    'use strict';

    var AutoallocateAction;

    /**
     * TEST123
     *
     * @export  oro/datagrid/action/autoallocate-Action
     * @class   oro.datagrid.action.AutoallocateAction
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