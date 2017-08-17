<?php

namespace CampusCRM\CampusCalendarBundle\Manager\CalendarEvent;

use Oro\Bundle\CalendarBundle\Manager\CalendarEvent\UpdateChildManager as BaseManager;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

class UpdateChildManager extends BaseManager
{
    /**
     * @param Calendar      $calendar
     * @param CalendarEvent $calendarEvent
     *
     * @return CalendarEvent
     */
    protected function createAttendeeCalendarEvent(Calendar $calendar, CalendarEvent $calendarEvent)
    {
        $attendeeCalendarEvent = new CalendarEvent();
        $attendeeCalendarEvent->setCalendar($calendar);
        $attendeeCalendarEvent->setParent($calendarEvent);

        $attendeeCalendarEvent->setTeachingWeek($calendarEvent->getTeachingWeek());
        $attendeeCalendarEvent->setSemester($calendarEvent->getSemester());
        $attendeeCalendarEvent->setOroEventname($calendarEvent->getOroEventname());

        $calendarEvent->addChildEvent($attendeeCalendarEvent);
        $attendeeCalendarEvent->setRelatedAttendee($attendeeCalendarEvent->findRelatedAttendee());

        $this->updateAttendeeCalendarEvent($calendarEvent, $attendeeCalendarEvent);

        return $attendeeCalendarEvent;
    }
}
