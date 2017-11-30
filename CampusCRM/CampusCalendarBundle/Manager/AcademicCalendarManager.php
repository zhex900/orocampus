<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 2/5/17
 * Time: 10:47 PM
 */

namespace CampusCRM\CampusCalendarBundle\Manager;

use Doctrine\ORM\EntityManager;

class AcademicCalendarManager
{
    /** @var EntityManager */
    protected $em;

    /* @var string */
    protected $current_semester;

    /** @var \DateTime $now */
    private $now;

    private $semester_dates;
    private $recess_dates;

    const DEFAULT_UNIVERSITY = 'UNSW';
    const SEMESTER = 'Semester';
    const RECESS = 'Teaching Recess';
    const SEMESTER_CODE = array('1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D');

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->now = new \DateTime('now');
        $this->current_semester = $this->getSemester($this->now, self::DEFAULT_UNIVERSITY);
    }

    /**
     * {@inheritdoc}
     *
     * Find the semester dates of the year in a given date.
     *
     * Assumption: Semester dates are all within in the same year.
     * However summer semesters rollover to the next year.
     *
     * @param \Datetime $date
     * @return array
     **/
    private function getSemesterDates(\Datetime $date)
    {
        return $this->searchSystemCalendar(self::SEMESTER, $date->format("Y"));
    }

    /**
     * {@inheritdoc}
     *
     * Find the recess dates of the year in a given date.
     *
     * @param \Datetime $date
     * @return array
     **/
    private function getRecessDates(\Datetime $date)
    {
        return $this->searchSystemCalendar(self::RECESS, $date->format("Y"));
    }

    /**
     * {@inheritdoc}
     *
     * Search the system calendar to find start and end date of
     * matching event title of a given year.
     *
     * Example:
     * searchSystemCalendar('Semester',2017)
     * This is to find all the events will the title Semester in year 2017.
     * If the system calendar have two calendars UNSW and USYD. This will be
     * returned.
     *
     * Array (
     *  [UNSW] =>   [1] =>
     *                  [0] => [DateTime Object $start, DateTime Object $end]
     *         =>   [2] =>
     *                  [0] => [DateTime Object $start, DateTime Object $end]
     *  [USYD] =>   [3] =>
     *                  [0] => [DateTime Object $start, DateTime Object $end]
     *         =>   [4] =>
     *                  [0] => [DateTime Object $start, DateTime Object $end]
     *
     * The first array key is the system calendar name which represents the university.
     *
     * The second array key is the semester number. So Semester 1 will have the key 1
     * Semester 4 will have the key 4. UNSW have Semester 1 and Semester 2. USYD have
     * Semester 3 and Semester 4.
     *
     * The third array is redundant. Its hard to change, so leave it there.
     *
     * The fourth array is a pair of dates. The first element is start and the second end.
     *
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
            throw new \Exception($key . ' period cannot be find in ' . $year . ' System Calendar!');
        }
        return $array;
    }

    /**
     * {@inheritdoc}
     *
     * Determines the academic semester of a given date.
     * Semester 1: 1 Jan to the date before the Semester 2.
     * Semester 2: Semester 2 until the date before the next Semester or 31 December.
     *
     * Semester start and end dates are stored in the System Calendar.
     *
     * return format: 2017A (Semester 1), 2017B (Semester 2), 2017C (Semester 3)
     *
     * @param \DateTime $date
     * @param string $university
     * @return string
     */
    public function getSemester($date, $university = self::DEFAULT_UNIVERSITY)
    {
        $start = $this->getSemesterStartDate($date, $university);
        return $start[0]->format("Y") . self::SEMESTER_CODE[$start[1]];
    }

    /**
     * {@inheritdoc}
     *
     * Given a date find the semester it belongs and return the start date of that
     * semester with the semester code.
     * Semester code: 1 refers to Semester 1. 2 refers to Semester 2.
     *
     * @param \DateTime $date
     * @param string $university
     * @return array [\DateTime, start date of the semester,
     *                  string, semester code in numbers]
     */
    public function getSemesterStartDate($date, $university = self::DEFAULT_UNIVERSITY)
    {
        $date->setTime(0, 0, 0);
        $semester = null;
        $this->semester_dates = $this->getSemesterDates($date);
        $semesters = array_keys($this->semester_dates[$university]);

        if (empty($semesters)) {
            throw new \Exception($university . ' Calendar does not exist.');
        }
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
        return array($this->semester_dates[$university][$semester][0][0], $semester);
    }

    /**
     * {@inheritdoc}
     * @param \DateTime $date
     * @return string
     */
    public function getTeachingWeek($date, $sem = null, $university = self::DEFAULT_UNIVERSITY)
    {
        file_put_contents('/tmp/weeks.log', 'date ' . $date->format('Y-m-d') . PHP_EOL, FILE_APPEND);

        $this->semester_dates = $this->getSemesterDates($date);
        $this->recess_dates = $this->getRecessDates($date);
        $date->setTime(0, 0, 0);

        if ($sem == null) {
            // find the semester key
            $sem = substr($this->getSemester($date), 4);
        }

        //  file_put_contents('/tmp/weeks.log', 'sem ' . $sem.PHP_EOL, FILE_APPEND);

        $sem_key = array_search($sem, self::SEMESTER_CODE);
        //file_put_contents('/tmp/weeks.log', 'sem key ' . $sem_key.PHP_EOL, FILE_APPEND);

        // get the start and end dates of the semester
        /** @var \DateTime $sem_start */
        $sem_start = $this->semester_dates[$university][$sem_key][0][0];
        file_put_contents('/tmp/weeks.log', 'start ' . $sem_start->format('Y-m-d') . PHP_EOL, FILE_APPEND);

        /** @var \DateTime $sem_end */
        $sem_end = $this->semester_dates[$university][$sem_key][0][1];
        file_put_contents('/tmp/weeks.log', '$sem_end ' . $sem_end->format('Y-m-d') . PHP_EOL, FILE_APPEND);

        file_put_contents('/tmp/weeks.log', 'compare ' . sizeof($this->recess_dates[$university][self::RECESS]) . PHP_EOL, FILE_APPEND);

        /** @var \DateTime $recess_start */
        if ($sem_key <= sizeof($this->recess_dates[$university][self::RECESS])) {

            $recess_start = $this->recess_dates[$university][self::RECESS][$sem_key - 1][0];
        } else {
            throw new \Exception($university . ' Calendar, semester (' . $this->getSemester($date) . ') recess dates does not exist.');
        }
        //file_put_contents('/tmp/weeks.log', '$recess_start ' . $recess_start->format('Y-m-d') . PHP_EOL, FILE_APPEND);

        /** @var \DateTime $recess_end */
        $recess_end = $this->recess_dates[$university][self::RECESS][$sem_key - 1][1];

        // Find the Monday of the week of the date.
        /** @var \Datetime $monday */
        $monday = new \DateTime();
        $monday->setTimestamp(strtotime("monday this week", $date->getTimestamp()));
        $monday->setTimezone($sem_start->getTimezone());
        file_put_contents('/tmp/weeks.log', '$monday ' . $monday->format('Y-m-d') . PHP_EOL, FILE_APPEND);

        $weeks = ($sem_start->diff($monday)->format('%a')) / 7;
        file_put_contents('/tmp/weeks.log', 'start ' . $sem_start->format('Y-m-d') . PHP_EOL, FILE_APPEND);
        file_put_contents('/tmp/weeks.log', '$sem_end ' . $sem_end->format('Y-m-d') . PHP_EOL, FILE_APPEND);
        file_put_contents('/tmp/weeks.log', 'date ' . $date->format('Y-m-d') . PHP_EOL, FILE_APPEND);

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
        return $this->current_semester;
    }

    /*
     * Find the start date of the next semester.
     *
     * return \DateTime
     */
    public function getNextSemesterStartDate()
    {
        $current_semester_code = array_flip(self::SEMESTER_CODE)[substr($this->current_semester, -1)];
        $this_year = $this->getSemesterDates($this->now);
        $next_sem = false;
        // look for the next semester in the current year
        foreach( $this_year[self::DEFAULT_UNIVERSITY] as $semester=>$dates ){
            if ($semester == $current_semester_code){
                $next_sem = true;
                continue;
            }
            if ($next_sem){
                return $dates[0][0];
            }
        }
        $next_year_date = new \DateTime('1st January Next Year');

        return $this->getSemesterStartDate($next_year_date)[0];
    }
}