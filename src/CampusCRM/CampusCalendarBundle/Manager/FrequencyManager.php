<?php

namespace CampusCRM\CampusCalendarBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\CalendarBundle\Entity\Attendee;

class FrequencyManager
{
    const SNAP_SHOT = 5;
    const BEFORE_SNAP_SHOT = 3;
    const AFTER_SNAP_SHOT = 2;
    const GAP = 5; // number of days
    const FIRST_TIME = '1st';
    const SECOND_TIME = '2st';
    const REGULAR = 'Regular';
    const IRREGULAR = 'Irregular';
    const DATE_FORMAT = 'Y-m-d';

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Attendee $attendee
     * @param Boolean $add
     */
    public function updateAttendanceFrequency( $attendee, $add=true)
    {
        /** @var Contact $contact */
        $contact = $attendee->getContact();
        /** @var CalendarEvent $calendar_event */
        $calendar_event = $attendee->getCalendarEvent();
        // Get all the same events that the contact have attended this semester
        $events = $this->getAttendedEvents($contact, $calendar_event);

        // Get all events that after the current event date.
        // Add the current event to the head of the array.
        $date = $calendar_event->getStart();

        $current_event = Array((0) => Array(
            'date' => $calendar_event->getStart()->format(self::DATE_FORMAT),
            'teaching_week' => $calendar_event->getTeachingWeek(),
            'attendee_id' => null
        ));

        $reset_events = array_filter($events, function ($val) use ($date) {
            return ($date) <= \DateTime::createFromFormat(self::DATE_FORMAT, $val['date']);
        });

        // Find the event before the current event date.
        $before_events = array_filter($events, function ($val) use ($date) {
            return ($date) > \DateTime::createFromFormat(self::DATE_FORMAT, $val['date']);
        });

        $attendance_count =1;

        if ( !empty($before_events)){
            $before_event = end($before_events);
            if ($before_event['attendance_count'] != null) {
                $attendance_count += (int)$before_event['attendance_count'];
            }
        }

        if( $add ) {
            $reset_events = array_merge($current_event, $reset_events);

        }else{
            // remove the first event.
            array_shift($reset_events);
        }

        $events = array_merge($before_events,$reset_events);

        // what if it is the same event?
        if ($add) {
            $count = $attendance_count;
        }else{
            $count = $attendance_count-1;
        }

        foreach ($reset_events as $reset_event) {

            $d= \DateTime::createFromFormat(self::DATE_FORMAT, $reset_event['date']);

            // find the frequency for this event.
            $freq = $this->findAttendanceFrequency($d , $reset_event['teaching_week'], $events);

            if ($reset_event['attendee_id'] != null) {
                // find the event attendee entity
                /** @var Attendee $rest_attendee */
                $rest_attendee = $this->em
                    ->getRepository('OroCalendarBundle:Attendee')
                    ->find($reset_event['attendee_id']);

                    ++$count;

                $this->commitChange($rest_attendee, $freq, $count);
            } else {
                // the current event have attendee_id as null
                $this->commitChange($attendee,$freq,$attendance_count);
            }
        }
    }

    private function commitChange(Attendee $attendee, $freq, $count){

        $attendee->setFrequency($freq);
        $attendee->setAttendanceCount($count);

        $unitOfWork = $this->em->getUnitOfWork();
        $unitOfWork->recomputeSingleEntityChangeSet(
            $this->em->getClassMetadata(Attendee::class),
            $attendee
        );
    }

    /*
     * Returns an array of events that a contact have attended.
     * @param Contact $contact
     * @param CalendarEvent $calendar_event
     * @return array
     */
    protected function getAttendedEvents($contact, $calendar_event)
    {
        /*
        * This raw SQL query returns a list of events that a
        * contact have attended this semester
        */
        $sql = '
                SELECT
                  e.title,
                  e.semester,
                  DATE (e.start_at) as date,
                  e.teaching_week,
                  a.display_name,
                  a.attendance_count,
                  a.user_id,
                  a.contact_id,
                  a.id as attendee_id,
                  e.id as event_id
                FROM oro_calendar_event_attendee AS a 
                  INNER JOIN oro_calendar_event AS e ON e.id = a.calendar_event_id
                WHERE a.contact_id= :id 
                AND e.semester= :sem
                AND e.title= :title
                ORDER BY date ASC
                ';

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute(array(
            'id' => $contact->getId(),
            'sem' => $calendar_event->getSemester(),
            'title' => $calendar_event->getTitle()));

        return $stmt->fetchAll();
    }

    /**
     * @param \DateTime $event_date
     * @param string $event_teaching_week
     * Events is an array of the same events that the contact have attended this semester
     * @param array $events
     *
     * @return string
     */
    public function findAttendanceFrequency($event_date, $event_teaching_week, $events)
    {

        $end = $event_date;
        // find the date that is 5 weeks ago.
        $begin = clone $event_date;
        $begin->sub(new \DateInterval('P5W'));

        // get the dates only
        $events = array_column($events,'date');
        file_put_contents('/tmp/freq.log', 'Current Event: '.
            $event_date->format(self::DATE_FORMAT).
            ' Begin: '. $begin->format(self::DATE_FORMAT).
            PHP_EOL, FILE_APPEND);

        file_put_contents('/tmp/freq.log', 'Events ' .
            print_r($events,true).
            PHP_EOL, FILE_APPEND);

        $DATE_FORMAT = self::DATE_FORMAT;
        // find all the events that is from the last 5 weeks until the event date
        $search_events = array_filter($events, function ($val) use ($begin,$end,$DATE_FORMAT) {
            return \DateTime::createFromFormat($DATE_FORMAT, $val) >= $begin &&
                \DateTime::createFromFormat($DATE_FORMAT, $val) <= $end;
        });

        file_put_contents('/tmp/freq.log', '$search_events ' .
            print_r($search_events,true).
            PHP_EOL, FILE_APPEND);

        // no previous events found.
        if (count($search_events)==0){
            return self::IRREGULAR;
        }
        $events_compare = $search_events;
        //remove the first event
        array_shift($events_compare);
        //push the current_event to the array.
        $events_compare[]=$event_date->format(self::DATE_FORMAT);
        $minimum_days= $this->getMinimumDays($event_teaching_week);

        file_put_contents('/tmp/freq.log', '$events_compare ' .
            print_r($events_compare,true).
            PHP_EOL, FILE_APPEND);

        $freq = array_sum(array_map(
            function ($before,$after) use ($minimum_days,$DATE_FORMAT){
                /** @var \DateTime $date_before */
                $date_before = \DateTime::createFromFormat($DATE_FORMAT, $before);
                /** @var \DateTime $date_after */
                $date_after = \DateTime::createFromFormat($DATE_FORMAT, $after);
                $diff = \date_diff($date_before, $date_after)->days;
                return $diff >= $minimum_days;
            },
            $events_compare,$search_events))+1;

        file_put_contents('/tmp/freq.log', 'FREQ: ' . $freq . PHP_EOL, FILE_APPEND);

        if ($freq >= $this->getMinimumDays($event_teaching_week)) {
            return self::REGULAR;
        } else {
            return self::IRREGULAR;
        }
    }

    /*
     * @param string $event_teaching_week
     * return int
     */
    protected function getMinimumDays($event_teaching_week)
    {
        $minimum_days = self::BEFORE_SNAP_SHOT;
        if ((int)$event_teaching_week > self::SNAP_SHOT) {
            $minimum_days = self::AFTER_SNAP_SHOT;
        }
        return $minimum_days;
    }
}