<?php

namespace CampusCRM\CampusCalendarBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

use Oro\Bundle\CalendarBundle\Controller\Api\Rest\CalendarEventController as BaseController;

/**
 * @RouteResource("calendarevent")
 * @NamePrefix("oro_api_")
 */
class CalendarEventController extends BaseController
{
    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('campus_calendar.calendar_event.form.handler.api');
    }
}
