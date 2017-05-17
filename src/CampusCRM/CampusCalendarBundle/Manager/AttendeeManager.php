<?php

namespace CampusCRM\CampusCalendarBundle\Manager;

use Oro\Bundle\CalendarBundle\Manager\AttendeeManager as BaseManager;
use Oro\Bundle\ContactBundle\Entity\Contact;

class AttendeeManager extends BaseManager
{
    /*
     * @param Contact $contact
     * @param array $attendees
     * @return Attendee $attendee
     */
    public function findAttendeeByContact($contact,$attendees){
        foreach ($attendees as $attendee){
            if ( $attendee->getContact() == $contact ) {
               return $attendee;
            }
        }
    }
}
