<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 2/5/17
 * Time: 10:47 PM
 */

namespace CampusCRM\CampusCalendarBundle\Provider;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;

class AcademicCalendar
{

    /** @var EntityManager */
    protected $em;

    /** @var Session */
    protected $session;

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

    /** @var \DateTime $now */
    private $now;

    /**
     * @param EntityManager $em
     * @param Session $session
     */
    public function __construct(EntityManager $em, Session $session)
    {
        $this->em = $em;
        $this->now = new \DateTime('now');
        $this->semester_dates = $this->getSemesterDates();
        $this->recess_dates = $this->getRecessDates();
        $this->session = $session;
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
     * @param string $key
     * @param int $year
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

        if (empty($array)) {
            throw new \Exception($key . ' period cannot be find in '. $year . ' System Calendar!');
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

        if (empty($semesters)) {
            throw new \Exception($university . ' Calendar does not exist.');
        }
        file_put_contents('/tmp/weeks.log', print_r($semesters).PHP_EOL, FILE_APPEND);


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
        file_put_contents('/tmp/weeks.log', 'date ' . $date->format('Y-m-d').PHP_EOL, FILE_APPEND);

        $date->setTime(0, 0, 0);

        if ($sem == null) {
            // find the semester key
            $sem = substr($this->getSemester($date), 4);
        }

        file_put_contents('/tmp/weeks.log', 'sem ' . $sem.PHP_EOL, FILE_APPEND);

        $sem_key = array_search($sem, self::SEMESTER_CODE);
        file_put_contents('/tmp/weeks.log', 'sem key ' . $sem_key.PHP_EOL, FILE_APPEND);

        // get the start and end dates of the semester
        /** @var \DateTime $sem_start */
        $sem_start = $this->semester_dates[$university][$sem_key][0][0];
        file_put_contents('/tmp/weeks.log', 'start ' . $sem_start->format('Y-m-d').PHP_EOL, FILE_APPEND);

        /** @var \DateTime $sem_end */
        $sem_end = $this->semester_dates[$university][$sem_key][0][1];
        file_put_contents('/tmp/weeks.log', '$sem_end ' . $sem_end->format('Y-m-d').PHP_EOL, FILE_APPEND);

        file_put_contents('/tmp/weeks.log', 'compare ' . sizeof($this->recess_dates[$university][self::RECESS]) . PHP_EOL, FILE_APPEND);

        /** @var \DateTime $recess_start */
        if ($sem_key <=sizeof($this->recess_dates[$university][self::RECESS])) {

            $recess_start = $this->recess_dates[$university][self::RECESS][$sem_key - 1][0];
        }else {
            file_put_contents('/tmp/weeks.log', '$recess_start aaa' . PHP_EOL, FILE_APPEND);

            $this->session->getFlashBag()->add('error', 'Calendar does not exist.');
            throw new \Exception($university . 'asdfd Calendar does not exist.');
            //file_put_contents('/tmp/weeks.log', '$recess_start aaa' . PHP_EOL, FILE_APPEND);
            //throw new RuntimeException('Calendar does not exist.');
        }
        file_put_contents('/tmp/weeks.log', '$recess_start ' . $recess_start->format('Y-m-d') . PHP_EOL, FILE_APPEND);

        /** @var \DateTime $recess_end */
        $recess_end = $this->recess_dates[$university][self::RECESS][$sem_key - 1][1];

        // Find the Monday of the week of the date.
        /** @var \Datetime $monday */
        $monday = new \DateTime();
        $monday->setTimestamp(strtotime("monday this week", $date->getTimestamp()));
        $monday->setTimezone($sem_start->getTimezone());
        file_put_contents('/tmp/weeks.log', '$monday ' . $monday->format('Y-m-d').PHP_EOL, FILE_APPEND);

        $weeks = ($sem_start->diff($monday)->format('%a')) / 7;
        file_put_contents('/tmp/weeks.log', 'start ' . $sem_start->format('Y-m-d').PHP_EOL, FILE_APPEND);
        file_put_contents('/tmp/weeks.log', '$sem_end ' . $sem_end->format('Y-m-d').PHP_EOL, FILE_APPEND);
        file_put_contents('/tmp/weeks.log', 'date ' . $date->format('Y-m-d').PHP_EOL, FILE_APPEND);

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
}