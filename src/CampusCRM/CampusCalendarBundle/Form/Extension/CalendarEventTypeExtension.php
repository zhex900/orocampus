<?php

namespace CampusCRM\CampusCalendarBundle\Form\Extension;

use CampusCRM\CampusCalendarBundle\Form\EventListener\CalendarEventTypeSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CalendarEventTypeExtension extends AbstractTypeExtension
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
                array(
                    'required' => false,
                    'data' => 'Default Title Jake',
                    'label' => 'oro.calendar.calendarevent.title.label'
                )
            )
            ->add(
                'attendees',
                'campus_calendar_event_attendees_select',
                [
                    'required' => false,
                    'label' => 'oro.calendar.calendarevent.attendees.label',
                    'layout_template' => $options['layout_template'],
                ]
            )
            ->add(
                'appendContacts',
                'oro_entity_identifier',
                array(
                    'class' => 'OroContactBundle:Contact',
                    'required' => false,
                    'mapped' => false,
                    'multiple' => true,
                )
            )
            ->add(
                'removeContacts',
                'oro_entity_identifier',
                array(
                    'class' => 'OroContactBundle:Contact',
                    'required' => false,
                    'mapped' => false,
                    'multiple' => true,
                )
            );

        file_put_contents('/tmp/new.log','CalendarEventTypeExtension: '. PHP_EOL,FILE_APPEND);

        $builder->addEventSubscriber(new CalendarEventTypeSubscriber($this->container));
    }
}
