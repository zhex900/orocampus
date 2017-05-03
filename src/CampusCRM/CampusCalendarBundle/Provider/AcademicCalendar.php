<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 2/5/17
 * Time: 10:47 PM
 */

namespace CampusCRM\CampusCalendarBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\ORM\Query\AST\Functions\SimpleFunction;

class AcademicCalendar {

    /** @var EntityManager */
    protected $em;

    /** @var array $semesters */
    // array ( key=>university name ,
    //          value = array ( key=>'1',
    //          value= array ('begin date', 'end date')))
    // this year's semester dates.
    private $semester_dates;

    const DEFAULT_UNIVERSITY = 'UNSW';
    const SEMESTER = 'Semester';
    const SEMESTER_CODE = array ('1'=>'A','2'=>'B','3'=>'C','4'=>'D');

    /** @var \DateTime $now */
    private $now;
    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->now = new \DateTime('now');
        $this->getSemesterDates();
    }

    /**
     * {@inheritdoc}
     * This is assuming semester dates are within the same year.
     **/
    private function getSemesterDates(){

        $qb =  $this->em->getRepository('OroCalendarBundle:CalendarEvent')
            ->createQueryBuilder('ce')
            ->select('ce.title','ce.start','ce.end','sc.name')
            ->innerJoin('ce.systemCalendar','sc')
            ->andWhere('ce.title LIKE :title')
            ->andWhere('YEAR(ce.start) = :year')
            ->setParameter('title', self::SEMESTER.'%')
            ->setParameter('year', $this->now->format("Y") )
            ->orderBy('ce.title')
            ->getQuery()
            ->getResult();

        foreach ($qb as &$semester) {
            $this->semester_dates[$semester['name']]
                                 [preg_replace('/'.self::SEMESTER.' /', '', $semester['title'])]
                                 []
                                = array($semester['start'], $semester['end']);
        }
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
    public function getSemester($date,$university=self::DEFAULT_UNIVERSITY){
    // is the date fall within any semesters dates, if yes which semester
        $semester=null;

        // what if no semester dates are available ?
        $semesters = array_keys($this->semester_dates[$university]);

        for ($i=0; $i < count($semesters); $i++){

            if ( // first semester
                ( $i == 0 and $date < $this->semester_dates[$university][$semesters[$i]][0][0])
                // last semester
                or ( $i==count($semesters)-1)
                // semesters in between the first and the last.
                or (( $date < $this->semester_dates[$university][$semesters[$i+1]][0][0]
                    and $date >= $this->semester_dates[$university][$semesters[$i]][0][0]))
            ){
                $semester=$semesters[$i];
                break;
            }
        }
        return $date->format("Y") . self::SEMESTER_CODE[$semester];
    }

    /**
     * {@inheritdoc}
     * @param \DateTime $date
     * @return Integer
     */
    public function getTeachingWeek($date){

    }

    public function getCurrentSemester(){
        return $this->getSemester($this->now, self::DEFAULT_UNIVERSITY);
    }
}