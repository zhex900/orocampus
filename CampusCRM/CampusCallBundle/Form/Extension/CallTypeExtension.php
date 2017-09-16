<?php

namespace CampusCRM\CampusCallBundle\Form\Extension;

use CampusCRM\CampusCallBundle\Form\EventListener\CallTypeSubscriber;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CallTypeExtension extends AbstractTypeExtension
{
    /** @var EntityManager */
    protected $em;

    /** @var ContainerInterface $container */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->container = $container;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' =>
                    [
                        'call_validation'
                    ],
            ]
        );
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'oro_call_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('direction')
            ->remove('call_type')
            ->remove('phoneNumber');

        $builder
            ->add('call_type', 'oro_enum_select', [
                'label'           => 'oro.call.call_type.label',
                'enum_code'       => 'call_type_source',
                'empty_value'     => 'Phone',
                'required'        => true
            ])
            ->add(
                'phoneNumber',
                'hidden',
                array(
                    'required' => false
                )
            );

        $builder->addEventSubscriber(new CallTypeSubscriber($this->em,$this->container));
    }
}
