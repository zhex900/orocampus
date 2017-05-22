<?php

namespace CampusCRM\CampusCalendarBundle\Controller;

use Oro\Bundle\CalendarBundle\Controller\CalendarEventController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
/**
 * @Route("/event")
 */
class CalendarEventController extends BaseController
{
    /**
     * @param CalendarEvent $entity
     * @param string        $formAction
     *
     * @return array
     */
    protected function update(CalendarEvent $entity, $formAction)
    {
        $saved = false;

        if ($this->get('campus_calendar.calendar_event.form.handler')->process($entity)) {
            if (!$this->getRequest()->get('_widgetContainer')) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.calendar.controller.event.saved.message')
                );

                return $this->get('oro_ui.router')->redirect($entity);
            }
            $saved = true;
        }

        return [
            'entity'     => $entity,
            'saved'      => $saved,
            'form'       => $this->get('campus_calendar.calendar_event.form.handler')->getForm()->createView(),
            'formAction' => $formAction
        ];
    }

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
        return array_merge(
                            parent::updateAction($entity),
                            ['attendance_selection' => true]);
    }
}
