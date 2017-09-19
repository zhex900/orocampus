<?php

namespace CampusCRM\CampusCalendarBundle\Manager;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    /** @var Logger $logger */
    protected $logger;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->logger = $container->get('logger');
    }

    /**
     * @param Attendee $attendee
     * @param string $add
     */
    public function updateAttendanceFrequency($attendee, $add)
    {
        /** @var Contact $contact */
        $contact = $attendee->getContact();
        /** @var CalendarEvent $calendar_event */
        $calendar_event = $attendee->getCalendarEvent();
        // Get all the same events that the contact have attended this semester
        $reset_events = $this->findAttendedEvents($contact, $calendar_event, null);

        // Get all events that after the current event date.
        // Add the current event to the head of the array.
        $date = $calendar_event->getStart();

        $current_event = Array((0) => Array(
            'date' => $calendar_event->getStart()->format(self::DATE_FORMAT),
            'event_id' => $calendar_event->getId(),
            'teaching_week' => $calendar_event->getTeachingWeek(),
            'attendee_id' => null
        ));

        if ($add == 'ADD') {
            $reset_events = array_merge($current_event, $reset_events);

        } elseif ($add == 'DELETE') {
            // remove the $current_event.
            $reset_events = array_filter($reset_events, function ($val) use ($calendar_event) {
                return ($calendar_event->getId() != $val['event_id']);
            });
        }

        file_put_contents('/tmp/event.log', PHP_EOL . '----- Events: ' . PHP_EOL, FILE_APPEND);

        // Find the event with the same event id and replace old event date with current event date.
        $reset_events = array_map(
            function ($v) use ($calendar_event) {
                if ($v['event_id'] == $calendar_event->getId()) {
                    $v['date'] = $calendar_event->getStart()->format(self::DATE_FORMAT);
                }
                return $v;
            },
            $reset_events
        );

        // Sort the events according to event date.
        usort($reset_events,
            function ($a, $b) {
                $t1 = strtotime($a['date']);
                $t2 = strtotime($b['date']);
                return $t1 - $t2;
            }
        );

        file_put_contents('/tmp/event.log', 'Attendee: ' . $attendee->getDisplayName() . PHP_EOL, FILE_APPEND);
        file_put_contents('/tmp/event.log', 'Current Event: ' . $calendar_event->getStart()->format(self::DATE_FORMAT) . ' id: ' . $calendar_event->getId(), FILE_APPEND);
        file_put_contents('/tmp/event.log', PHP_EOL . 'Attended Events: ' . PHP_EOL, FILE_APPEND);

        file_put_contents('/tmp/event.log', PHP_EOL . 'Reset_events: ' . PHP_EOL, FILE_APPEND);
        file_put_contents('/tmp/event.log', PHP_EOL . '' . PHP_EOL, FILE_APPEND);

        $count = 0;
        foreach ($reset_events as $reset_event) {
            ++$count;
            $d = \DateTime::createFromFormat(self::DATE_FORMAT, $reset_event['date']);

            // find the frequency for this event.
            $freq = $this->findAttendanceFrequency($d, $reset_event['teaching_week'], $reset_events);

            if ($reset_event['attendee_id'] != null) {
                // find the event attendee entity
                /** @var Attendee $rest_attendee */
                $rest_attendee = $this->em
                    ->getRepository('OroCalendarBundle:Attendee')
                    ->find($reset_event['attendee_id']);

                $this->commitChange($rest_attendee, $freq, $count);
                file_put_contents('/tmp/event.log', $reset_event['date'] . ' N:' . $count . PHP_EOL, FILE_APPEND);

            } else {
                // the current event have attendee_id as null
                $this->commitChange($attendee, $freq, $count);
                file_put_contents('/tmp/event.log', $reset_event['date'] . ' ' . $count . PHP_EOL, FILE_APPEND);
            }

        }
    }

    /**
     * @param string $freq
     * @param integer $count
     */
    private function commitChange(Attendee $attendee, $freq, $count)
    {

        $attendee->setFrequency($freq);
        $attendee->setAttendanceCount($count);

        $unitOfWork = $this->em->getUnitOfWork();
        $unitOfWork->recomputeSingleEntityChangeSet(
            $this->em->getClassMetadata(Attendee::class),
            $attendee
        );
    }

    /*
   * Returns an array of all the events that a contact have attended in a semester.
   * @param Contact $contact
   * @param CalendarEvent $calendar_event
   * @param string $semester
   * @return array
   */
    public function findAttendedEvents(Contact $contact, CalendarEvent $calendar_event = null, $semester = null)
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
                AND e.semester= :sem ' . (!isset($semester) ? 'AND e.title= :title' : '') . '
                ORDER BY date ASC
                ';

        $stmt = $this->em->getConnection()->prepare($sql);
        $param = array(
            'id' => $contact->getId(),
            'sem' => $semester);

        if (!isset($semester)) {
            $param = array_merge($param, array('title' => $calendar_event->getTitle()));
        }

        $stmt->execute($param);

        return $stmt->fetchAll();
    }

    /*
     * This is a wrapper function for findAttendanceFrequencyCore
     * It filter out events not within with the 5 week date range.
     *
     * @param \DateTime $event_date
     * @param string $event_teaching_week Teaching week of the event date
     * @param array $search_events Events
     * @param int $algorithm 0 for algorithm 1. 1 for algorithm 2.
     *
     * @return string 'Regular' or 'Irregular'
     */
    public function findAttendanceFrequency(
        \DateTime $event_date,
        $event_teaching_week,
        $events, $algorithm = 1)
    {
        // set look back date range
        $end = $event_date;
        // find the date that is 5 weeks ago.
        $begin = clone $event_date;
        $begin->sub(new \DateInterval('P5W'));

        // get the dates only
        $events = array_column($events, 'date');

        $this->logger->debug('FrequencyManager: Date range (' .
            $begin->format(self::DATE_FORMAT) .
            ',' . $event_date->format(self::DATE_FORMAT) . '). Events: ', $events);

        // find all the events that is from the last 5 weeks until the event date
        $DATE_FORMAT = self::DATE_FORMAT;
        $search_events = array_filter($events, function ($val) use ($begin, $end, $DATE_FORMAT) {
            return \DateTime::createFromFormat($DATE_FORMAT, $val) >= $begin &&
                \DateTime::createFromFormat($DATE_FORMAT, $val) <= $end;
        });

        // no previous events found return irregular
        if (count($search_events) == 0) {
            return self::IRREGULAR;
        }
        return $this->findAttendanceFrequencyCore($event_date, $event_teaching_week, $search_events, $algorithm);
    }

    /**
     * In the past 5 weeks, determine whether a person have attended events regularly or irregularly
     * This is a complex algorithm.
     *
     * Regularity is calculated by counting the number of attendances
     * in the past 5 weeks. This counter is called frequency. An attendance is counted only when
     * there is a 5 day gap since the last attendance. For example, event on 1 Sep and 2 Sep will
     * have a frequency count of 1. Event on 1 Sep and 6 Sep will have a frequency count 2.
     *
     * Algorithm 1:
     * This is to calculate the regularly of the same event name.
     * $event_date is the date of the event.
     * $events is an array of dates of the same event.
     * For example, event appointment is on 1 Sep 2017. Determine
     * whether John is regular to event appointment.
     *
     * Algorithm 2:
     * This is to calculate whether a person is regularly attending meetings/events.
     * Algorithm 1 is looking at the same event.
     * Algorithm 2 is looking at any events.
     * $event_date is today's date
     * $events is all the events a contact have attended this semester
     *
     * @param \DateTime $event_date
     * @param string $event_teaching_week Teaching week of the event date
     * @param array $search_events Events
     * @param int $algorithm 0 for algorithm 1. 1 for algorithm 2.
     *
     * @return string 'Regular' or 'Irregular'
     */
    protected function findAttendanceFrequencyCore(\DateTime $event_date,
                                                   $event_teaching_week,
                                                   $search_events, $algorithm = 1)
    {
        $events_compare = $search_events;
        /*
         * This algorithm transforms the event dates into two arrays.
         * Like this:
         * Events = [ A, B, C, D]
         *      Array 1     Array 2
         * $search_events  $events_compare
         *      A             B
         *      B             C
         *      C             D
         * Find out the date difference the elements in both arrays.
         * Calculate the date difference between A and B, B and C, C and D.
         * If the the date difference is greater and equals to the gap
         * date (5 days) increment the frequency counter
         *
         */

        // Both algorithms need to remove the first event in array 2 $events_compare
        array_shift($events_compare);
        if ($algorithm) {
            /*
             * Algorithm 1:
             * $event_date is a new event. So this event does not exist in the events array.
             * Events ($events) array is a list of past events with the same event name.
             * The new event ($event_date) need to be pushed to the back of the array.
             * First element needs to be removed. See the example below.
             *
             * $event_date = E
             * Events = [ A, B, C, D]
             *      Array 1     Array 2
             * $search_events  $events_compare
             *      A             B
             *      B             C
             *      C             D
             *      D             E
             */
            $events_compare[] = $event_date->format(self::DATE_FORMAT);
        } else {
            /*
             * Algorithm 2:
             * $event_date is not an event date. It is today's date. Therefore,
             * the frequency calculation only need to look at the events dates.
             * Events = [ A, B, C, D]
             *      Array 1     Array 2
             * $search_events  $events_compare
             *      A             B
             *      B             C
             *      C             D
             */
            array_pop($search_events);
        }

        $this->logger->debug('FrequencyManager: Compare events: ', array_combine($events_compare, $search_events));
        $DATE_FORMAT = self::DATE_FORMAT;
        $freq = array_sum(array_map(
                function ($before, $after) use ($DATE_FORMAT) {
                    /** @var \DateTime $date_before */
                    $date_before = \DateTime::createFromFormat($DATE_FORMAT, $before);
                    /** @var \DateTime $date_after */
                    $date_after = \DateTime::createFromFormat($DATE_FORMAT, $after);
                    $diff = \date_diff($date_before, $date_after)->days;
                    return $diff >= self::GAP;
                },
                $events_compare, $search_events)) + 1;

        $this->logger->debug('FrequencyManager: Freq count: ' . $freq);

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

    public function today()
    {
        $today = new \DateTime();
        $today->setTimezone(new \DateTimeZone('UTC'));
        return $today;
    }

    public function getRegular()
    {
        return self::REGULAR;
    }
}