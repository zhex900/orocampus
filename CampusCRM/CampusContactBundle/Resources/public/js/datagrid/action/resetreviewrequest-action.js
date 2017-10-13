/*global define*/
define([
    'oro/datagrid/action/mass-action',
    'orotranslation/js/translator'
], function (MassAction) {
    'use strict';

    var ResetReviewRequestAction;

    /**
     * @export  oro/datagrid/action/resetreviewrequest-action
     * @class   oro.datagrid.action.ResetReviewRequestAction
     * @extends oro.datagrid.action.MassAction
     */
    ResetReviewRequestAction = MassAction.extend({
        defaultMessages: {
            confirm_title: 'Mass Action Reset Review Request Confirmation',
            confirm_content: 'Are you sure you want to do this?',
            confirm_ok: 'Yes, do it',
            confirm_cancel: 'Cancel',
        }
    });
    return ResetReviewRequestAction;
});