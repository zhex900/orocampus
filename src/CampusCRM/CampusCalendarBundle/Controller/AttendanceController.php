<?php

namespace CampusCRM\CampusCalendarBundle\Controller;

use CampusCRM\CampusCalendarBundle\Controller\CalendarEventController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

class AttendanceController extends BaseController
{
    /**
     * @Route(
     *          "/attendance/update/{id}",
     *           name="oro_calendar_event_attendance",
     *           requirements={"id"="\d+"},
     *           condition="request.get('_widgetContainer')"
     *       )
     * @Template
     * @Acl(
     *      id="oro_calendar_event_attendance",
     *      type="entity",
     *      class="OroCalendarBundle:CalendarEvent",
     *      permission="EDIT",
     *      group_name=""
     * )
     * @AclAncestor("oro_calendar_event_update")
     */
    public function updateAction(CalendarEvent $entity)
    {
        return parent::updateAction($entity);
    }
}
