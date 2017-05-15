<?php

namespace CampusCRM\CampusCalendarBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CalendarBundle\Entity\Attendee;

class CalendarEventTypeExtension extends AbstractTypeExtension
{
    /** @var  Container */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

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
        $builder->remove('attendees');
        $builder
            ->add(
                'title',
                'hidden',
                [
                    'required' => true,
                    'label'    => 'oro.calendar.calendarevent.title.label'
                ]
            )
            ->add(
                'attendees',
                'campus_calendar_event_attendees_select',
                [
                    'required' => false,
                    'label'    => 'oro.calendar.calendarevent.attendees.label',
                    'layout_template' => $options['layout_template'],
                ]
            );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var CalendarEvent $calendar_event */
                $calendar_event = $event->getData();
                $calendar_event->setTitle($calendar_event->getOroEventname());

                if ($calendar_event->getSystemCalendar() == null ){
                    $sem = $this
                        ->container
                        ->get('academic_calendar')
                        ->getSemester($calendar_event->getStart());
                    $calendar_event->setSemester($sem);

                    $calendar_event
                        ->setTeachingWeek($this
                            ->container
                            ->get('academic_calendar')
                            ->getTeachingWeek($calendar_event->getStart()),$sem);
                }
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
