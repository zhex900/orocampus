<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 11/8/17
 * Time: 10:31 AM
 */

namespace CampusCRM\CampusContactBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\AjaxMassAction;

class ResetReviewRequestMassAction extends AjaxMassAction
{
    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'oro_contact.mass_action.handler.resetreviewrequest';
        }

        if (empty($options['frontend_type'])) {
            $options['frontend_type'] = 'resetreviewrequest';
        }
        return parent::setOptions($options);
    }
}