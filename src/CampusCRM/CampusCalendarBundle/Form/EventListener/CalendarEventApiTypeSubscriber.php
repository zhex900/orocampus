<?php

namespace CampusCRM\CampusCalendarBundle\Form\EventListener;

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
            FormEvents::SUBMIT  => 'submitData',
            FormEvents::PRE_SET_DATA  => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function submitData(FormEvent $event)
    {
        $this->setTitle($event);
      //  $this->setAcademicCalendar($event);
      //  $this->syncContactAttendees($event);
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

    private function setTitle(FormEvent $event){
        /** @var CalendarEvent $calendar_event */
        $calendar_event = $event->getData();

        $event->getData()->setTitle($calendar_event->getOroEventname());
    }

    private function setAcademicCalendar(FormEvent $event){

        /** @var CalendarEvent $calendar_event */
        $calendar_event = $event->getData();

        if ($calendar_event->getCalendar() instanceof Calendar) {
            $sem = $this
                ->container
                ->get('academic_calendar')
                ->getSemester($calendar_event->getStart());
            $calendar_event->setSemester($sem);

            $calendar_event
                ->setTeachingWeek($this
                    ->container
                    ->get('academic_calendar')
                    ->getTeachingWeek($calendar_event->getStart()), $sem);
        }
    }
}
