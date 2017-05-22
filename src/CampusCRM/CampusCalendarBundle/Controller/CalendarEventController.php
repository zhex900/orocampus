<?php

namespace CampusCRM\CampusCalendarBundle\Controller;

use Oro\Bundle\CalendarBundle\Controller\CalendarEventController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

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
        $select = false;

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

        if( $this->getRequest()->get('select')){
            $select = true;
        }

        return [
            'entity'     => $entity,
            'saved'      => $saved,
            'form'       => $this->get('campus_calendar.calendar_event.form.handler')->getForm()->createView(),
            'formAction' => $formAction,
            'attendance_button' => $select
        ];
    }
}
