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
            confirm_title: 'Execution TEST123',
            confirm_content: 'Are you sure you want to do TEST123?',
            confirm_ok: 'Yes, do it',
            confirm_cancel: 'Cancel',
            success: 'Action performed.',
            error: 'Action is not performed.',
            empty_selection: 'Please, select item to perform action.'
        }
    });

    return AutoallocateAction;
});