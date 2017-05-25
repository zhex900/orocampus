<?php

namespace CampusCRM\CampusCalendarBundle\Form\Extension;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Oro\Bundle\ContactBundle\Entity\Contact;

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

        $builder
            ->add(
                'appendContacts',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroContactBundle:Contact',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            )
            ->add(
                'removeContacts',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroContactBundle:Contact',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var CalendarEvent $calendar_event */
                $calendar_event = $event->getData();

                $calendar_event->setTitle($calendar_event->getOroEventname());

                if ($calendar_event->getCalendar() instanceof Calendar ){
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

                /*
                 * Add and remove contacts from the attendence selection grid.
                 * Grid name is attendance-contacts-grid
                 */
                /** @var FormInterface $form */
                $form = $event->getForm();
                $appendContacts = $form->get('appendContacts')->getData();
                $removeContacts = $form->get('removeContacts')->getData();

                foreach ($appendContacts as $appendContact) {
                    if ( $appendContact instanceof Contact ) {
                        /** @var Attendee $attendee */
                        $attendee = $this->container
                            ->get('campus_calendar.attendee_manager')
                            ->createAttendee($appendContact);
                        $calendar_event->addAttendee($attendee);
                    }
                }
                foreach ($removeContacts as $removeContact) {
                    if ( $removeContact instanceof Contact ) {
                        /** @var Attendee $attendee */
                        $attendee = $this->container
                            ->get('campus_calendar.attendee_manager')
                            ->findAttendeeByContact(
                            $removeContact,
                            $calendar_event->getAttendees()
                        );
                        $calendar_event->removeAttendee($attendee);
                    }
                }

                // Add calendar owner as attendee

                /** @var Attendee $attendee */
                $attendee = $this->container
                    ->get('campus_calendar.attendee_manager')
                    ->createAttendee($calendar_event->getCalendar()->getOwner()->getContact());
                if( $calendar_event->getEqualAttendee($attendee) == null ) {
                    $calendar_event->addAttendee($attendee);
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
