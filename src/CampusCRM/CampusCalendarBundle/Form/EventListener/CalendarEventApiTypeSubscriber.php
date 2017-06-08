<?php

namespace CampusCRM\CampusCalendarBundle\Form\EventListener;

use CampusCRM\EventNameBundle\Entity\EventName;
use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Symfony\Component\Form\FormInterface;

class CalendarEventApiTypeSubscriber implements EventSubscriberInterface
{
    /** @var  ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmitData',
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmitData(FormEvent $event)
    {
        $this->setTitle($event);
        $this->setAcademicCalendar($event);
        // $this->syncContactAttendees($event);
    }

    /**
     * PRE_SET_DATA event handler
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $form->remove('teaching_week');
        $form->remove('semester');
    }

    private function setTitle(FormEvent $event)
    {

        $data = $event->getData();
        $eventname_id = $data['oro_eventname'];
        /** @var EventName $event_name */
        $event_name = $this
            ->container
            ->get('doctrine')
            ->getRepository('EventNameBundle:EventName')
            ->find($eventname_id);

        $data['title'] = $event_name->getName();
        $event->setData($data);
    }

    private function setAcademicCalendar(FormEvent $event)
    {
        $data = $event->getData();
        $time_stamp = strtotime($data['start']);
        $start = new \DateTime("@$time_stamp");
        $sem = $this->container->get('academic_calendar')->getSemester($start);
        $teaching_week = $this->container->get('academic_calendar')->getTeachingWeek($start, substr($sem,4));
        $data['semester']=$sem;
        $data['teaching_week'] =$teaching_week;

        $event->setData($data);
    }
}
