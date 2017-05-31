<?php

namespace CampusCRM\CampusCalendarBundle\Twig;

use Doctrine\ORM\EntityManager;

class AttendanceExtension extends \Twig_Extension
{
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
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_attendance', [$this, 'getAttendance']),
        ];
    }

    /**
     * Returns the attendance for an event.
     *
     * @param int $id
     *
     * @return array of contact ids
     *
     */

    public function getAttendance($id){
        $result = $this->em->getRepository('OroCalendarBundle:CalendarEvent')
            ->createQueryBuilder('ce')
            ->select('contact.id')
            ->innerJoin('ce.attendees','attendees')
            ->innerJoin('attendees.contact','contact')
            ->andWhere('ce.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        foreach ($result as $r){
            file_put_contents('/tmp/a.log',$r['id'].' ',FILE_APPEND);
        }
        return array_values($result);
    }
}
