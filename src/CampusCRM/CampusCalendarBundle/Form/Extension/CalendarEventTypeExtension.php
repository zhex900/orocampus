<?php

namespace CampusCRM\CampusCalendarBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

class CalendarEventTypeExtension extends AbstractTypeExtension
{

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'oro_calendar_event';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('title');
        $builder->remove('title');
        $builder
            ->add(
                'title',
                'hidden',
                [
                    'required' => true,
                    'label'    => 'oro.calendar.calendarevent.title.label'
                ]
            );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var CalendarEvent $calendar_event */
                $calendar_event = $event->getData();
                $calendar_event->setTitle($calendar_event->getOroEventname());
                // get the service.

                // $week = findWeek($calendar_event->getStart());
                //$semester = findSemester($calendar_event->getStart());
                // $calendar_event->setTeachingWeek($week);
                //$calendar_event->setSemester($semester);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $form->remove('teaching_week');
                $form->remove('semester');
            }
        );
    }
}
