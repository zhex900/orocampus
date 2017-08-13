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
            confirm_title: 'oro.contact.autoallocate.action.confirm_title',
            confirm_content: 'oro.contact.autoallocate.action.confirm_content',
            confirm_ok: 'oro.contact.autoallocate.action.confirm_ok',
            confirm_cancel: 'oro.contact.autoallocate.action.confirm_cancel'
        }
    });

    return AutoallocateAction;
});