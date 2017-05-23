<?php

namespace CampusCRM\CampusActivityBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Oro\Bundle\ActivityBundle\Controller\Api\Rest\ActivityContextController as BaseController;

/**
 * @RouteResource("activity_context")
 * @NamePrefix("oro_api_")
 */
class ActivityContextController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('campus_activity.manager.activity_context.api');
    }
}
