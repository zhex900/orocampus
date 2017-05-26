<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 2/5/17
 * Time: 10:47 PM
 */

namespace CampusCRM\CampusCalendarBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\ORM\Query\AST\Functions\SimpleFunction;

class AcademicCalendar
{

    /** @var EntityManager */
    protected $em;

    /** @var array $semesters */
    // array ( key=>university name ,
    //          value = array ( key=>'1',
    //          value= array ('begin date', 'end date')))
    // this year's semester dates.
    private $semester_dates;
    private $recess_dates;

    const DEFAULT_UNIVERSITY = 'UNSW';
    const SEMESTER = 'Semester';
    const RECESS = 'Teaching Recess';
    const SEMESTER_CODE = array('1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D');
    const SNAP_SHOT = 5;
    const BEFORE_SNAP_SHOT = 3;
    const AFTER_SNAP_SHOT = 2;
    const GAP = 5; // number of days
    const FIRST_TIME = '1st';
    const SECOND_TIME = '2st';
    const REGULAR = 'Regular';
    const IRREGULAR = 'Irregular';
    const DATE_FORMAT = 'Y-m-d';

    /** @var \DateTime $now */
    private $now;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->now = new \DateTime('now');
        $this->semester_dates = $this->getSemesterDates();
        $this->recess_dates = $this->getRecessDates();
    }

    /**
     * {@inheritdoc}
     * This is assuming semester dates are within the same year.
     **/
    private function getSemesterDates()
    {

        return $this->searchSystemCalendar(self::SEMESTER, $this->now->format("Y"));

    }

    /**
     * {@inheritdoc}
     * Find the recess week
     * array(1) { ["UNSW"]=>
     * array(1) { ["Teaching Recess"]=>
     * array(2) { [0]=>
     * array(2) { [0]=> object(DateTime)#2317 (3) { ["date"]=> string(26) "2017-04-17 00:00:00.000000" ["timezone_type"]=> int(3) ["timezone"]=> string(3) "UTC" }
     *            [1]=> object(DateTime)#2309 (3) { ["date"]=> string(26) "2017-04-23 00:00:00.000000" ["timezone_type"]=> int(3) ["timezone"]=> string(3) "UTC" } }
     *            [1]=>
     * array(2) { [0]=> object(DateTime)#2360 (3) { ["date"]=> string(26) "2017-09-25 00:00:00.000000" ["timezone_type"]=> int(3) ["timezone"]=> string(3) "UTC" }
     *            [1]=> object(DateTime)#2356 (3) { ["date"]=> string(26) "2017-10-01 00:00:00.000000" ["timezone_type"]=> int(3) ["timezone"]=> string(3) "UTC" }
     * } } } }
     **/
    private function getRecessDates()
    {

        return $this->searchSystemCalendar(self::RECESS, $this->now->format("Y"));

    }

    /**
     * {@inheritdoc}
     * @param String $field
     * @parma int $year
     * @param array $array
     * @return array
     *
     **/
    private function searchSystemCalendar($key, $year)
    {

        $result = $this->em->getRepository('OroCalendarBundle:CalendarEvent')
            ->createQueryBuilder('ce')
            ->select('ce.title', 'ce.start', 'ce.end', 'sc.name')
            ->innerJoin('ce.systemCalendar', 'sc')
            ->andWhere('ce.title LIKE :title')
            ->andWhere('YEAR(ce.start) = :year')
            ->setParameter('title', $key . '%')
            ->setParameter('year', $year)
            ->orderBy('ce.title')
            ->getQuery()
            ->getResult();

        $array = null;

        foreach ($result as &$item) {
            /** @var \DateTime $start */
            $start = $item['start'];
            /** @var \DateTime $end */
            $end = $item['end'];
            $array[$item['name']]
            [preg_replace('/' . $key . ' /', '', $item['title'])]
            []
                = array($start->setTime(0, 0, 0),
                $end->setTime(0, 0, 0));
        }
        return $array;
    }

    /**
     * {@inheritdoc}
     * @param \DateTime $date
     * @param String $university
     * @return String
     *
     * Determines the academic semester of a given date.
     * Semester 1: 1 Jan to the date before the Semester 2.
     * Semester 2: Semester 2 until the date before the next Semester or 31 December.
     *
     * Semester dates are stored in the System Calendar.
     *
     * Semester values are in this format: 2017A (Semester 1), 2017B (Semester 2), 2017C (Semester 3)
     */
    public function getSemester($date, $university = self::DEFAULT_UNIVERSITY)
    {
        $date->setTime(0, 0, 0);
        // what if $university don't exsit

        // what if $date year is different to the current year?

        // what if the semester calendar is not setup?

        // is the date fall within any semesters dates, if yes which semester
        $semester = null;

        // what if no semester dates are available ?
        $semesters = array_keys($this->semester_dates[$university]);

        for ($i = 0; $i < count($semesters); $i++) {

            if ( // first semester
                ($i == 0 and $date < $this->semester_dates[$university][$semesters[$i]][0][0])
                // last semester
                or ($i == count($semesters) - 1)
                // semesters in between the first and the last.
                or (($date < $this->semester_dates[$university][$semesters[$i + 1]][0][0]
                    and $date >= $this->semester_dates[$university][$semesters[$i]][0][0]))
            ) {
                $semester = $semesters[$i];
                break;
            }
        }
        return $date->format("Y") . self::SEMESTER_CODE[$semester];
    }

    /**
     * {@inheritdoc}
     * @param \DateTime $date
     * @return string
     */
    public function getTeachingWeek($date, $sem = null, $university = self::DEFAULT_UNIVERSITY)
    {
        $date->setTime(0, 0, 0);

        if ($sem == null) {
            // find the semester key
            $sem = substr($this->getSemester($date), 4);
        }

        $sem_key = array_search($sem, self::SEMESTER_CODE);

        // get the start and end dates of the semester
        /** @var \DateTime $sem_start */
        $sem_start = $this->semester_dates[$university][$sem_key][0][0];
        /** @var \DateTime $sem_end */
        $sem_end = $this->semester_dates[$university][$sem_key][0][1];
        /** @var \DateTime $recess_start */
        $recess_start = $this->recess_dates[$university][self::RECESS][$sem_key - 1][0];
        /** @var \DateTime $recess_end */
        $recess_end = $this->recess_dates[$university][self::RECESS][$sem_key - 1][1];

        // Find the Monday of the week of the date.
        /** @var \Datetime $monday */
        $monday = new \DateTime();
        $monday->setTimestamp(strtotime("monday this week", $date->getTimestamp()));
        $monday->setTimezone($sem_start->getTimezone());

        $weeks = ($sem_start->diff($monday)->format('%a')) / 7;

        // if the date is not within the semester period.
        if ($date < $sem_start or $date > $sem_end) {
            return -1;
        } // if the date is within recess period.
        elseif ($date >= $recess_start and $date <= $recess_end) {
            return self::RECESS;
        } // if the date is before recess
        elseif ($date < $recess_start) {
            return $weeks;
        } else {
            return $weeks - 1;
        }
    }

    public function getCurrentSemester()
    {
        return $this->getSemester($this->now, self::DEFAULT_UNIVERSITY);
    }

    /**
     * @param Contact $contact
     * @param CalendarEvent $calendar_event
     * @return string
     */
    public function findAttendanceFrequency($contact, $calendar_event)
    {
        file_put_contents('/tmp/freq.log', $contact->getFirstName() . PHP_EOL, FILE_APPEND);

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
                  a.user_id,
                  a.contact_id
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

        $result = $stmt->fetchAll();
        $size = count($result);

        $end = $calendar_event->getStart();
        // find the date that is 5 weeks ago.
        $begin = clone $calendar_event->getStart();
        $begin->sub(new \DateInterval('P5W'));

        /*
         * FIRST_TIME and SECOND_TIME only works if there are no events
         * after the current one.
         */
        if ($size == 0) {
            return self::FIRST_TIME;
        } elseif ($size == 1) {
            return self::SECOND_TIME;
        } else {
            $freq = 1; // including the current event.
            for ($i = 0; $i < $size; ++$i) {
                $item = $result[$i];

                /** @var \DateTime $date */
                $date = \DateTime::createFromFormat(self::DATE_FORMAT, $item['date']);

                file_put_contents('/tmp/freq.log', $i . ' of ' . $size . ' '.
                    $item['title'] . ' ' .
                    $item['date'] . ' ' .
                    $item['teaching_week'] . ' ' .
                    $item['display_name'] . ' ' .
                    $item['user_id'] . ' ' .
                    $item['contact_id'] .
                    'begin: '. $begin->format(self::DATE_FORMAT) .  ' '.
                    'end: '. $end->format(self::DATE_FORMAT).  ' ', FILE_APPEND);

                // look into the events of the from the last 5 weeks until the event date
                if ($date >= $begin && $date <= $end) {
                    // is there something to compare?
                    if ($i <= $size - 2) {
                        $next = $result[$i + 1]; // compare with the next item
                        if ($this->compare($item, $next, self::GAP)) {
                            $freq++;
                        }
                    } // last item
                    elseif ($i == $size - 1) {
                        if ($i-1>=0){ //look back by one
                            $prev=$result[$i-1];
                            //compare with the previous item
                            if ($this->compare($item, $prev, self::GAP)) {
                                $freq++;
                            }
                        }
                    }
                }
                file_put_contents('/tmp/freq.log', '->Freq: ' . $freq . PHP_EOL, FILE_APPEND);
            }

            file_put_contents('/tmp/freq.log', 'Total Freq: ' . $freq . PHP_EOL, FILE_APPEND);

            if ($freq >= $this->getMinimumDays($calendar_event)) {
                return self::REGULAR;
            } else {
                return self::IRREGULAR;
            }
        }
    }

    protected function compare($before, $after, $minimum_days)
    {
        /** @var \DateTime $date_before */
        $date_before = \DateTime::createFromFormat(self::DATE_FORMAT, $before['date']);
        /** @var \DateTime $date_after */
        $date_after = \DateTime::createFromFormat(self::DATE_FORMAT, $after['date']);
        $diff = \date_diff($date_before, $date_after)->days;

        return $diff >= $minimum_days;
    }

    /*
     * @param CalendarEvent $calendar_event
     * return int
     */
    protected function getMinimumDays($calendar_event)
    {
        $minimum_days = self::BEFORE_SNAP_SHOT;
        if ((int)$calendar_event->getTeachingWeek() > self::SNAP_SHOT) {
            $minimum_days = self::AFTER_SNAP_SHOT;
        }
        return $minimum_days;
    }
}