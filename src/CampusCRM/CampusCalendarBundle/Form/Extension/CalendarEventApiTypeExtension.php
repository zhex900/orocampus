<?php

namespace CampusCRM\CampusCalendarBundle\Form\Extension;

use CampusCRM\CampusCalendarBundle\Form\EventListener\CalendarEventApiTypeSubscriber;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use CampusCRM\CampusCalendarBundle\Form\EventListener\AttendeesSubscriber;

class CalendarEventApiTypeExtension extends AbstractTypeExtension
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
        return 'oro_calendar_event_api';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'allow_extra_fields'    => true,
                'allow_change_calendar' => false,
                'layout_template'       => false,
                'data_class'            => 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'intention'             => 'calendar_event',
                'csrf_protection'       => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('oro_eventname');

        $builder->add('oro_eventname', 'entity', array(
            'class' => 'CampusCRM\EventNameBundle\Entity\EventName',
            'query_builder' => function(EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('e');
                // the function returns a QueryBuilder object
                return $qb
                    // find all event name where system calendar is false
                    ->where($qb->expr()->neq('e.system_calendar', '?1'))
                    ->setParameter('1', '1')
                    ->orderBy('e.name', 'ASC')
                    ;
            },
        ));

        $builder->addEventSubscriber(new CalendarEventApiTypeSubscriber($this->container));
    }
}
