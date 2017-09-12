<?php

namespace CampusCRM\CampusContactBundle\Form\Extension;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampusContactTypeExtension extends AbstractTypeExtension
{
    /** @var String */
    protected $current_semester;

    /** @var  ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        try {
            $this->current_semester = $this->container
                ->get('academic_calendar')
                ->getCurrentSemester();
        } catch (\Exception $e) {
            $this->container->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'Oro\Bundle\ContactBundle\Entity\Contact',
                'cascade_validation'   => true,
                'validation_groups'    =>
                    [
                        'contact_info_check'
                    ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('gender')
            ->remove('lastName')
            ->remove('firstName');

        $builder
            ->add('firstName', 'text', array('required' => true, 'label' => 'oro.contact.first_name.label'))
            ->add('lastName', 'text', array('required' => true, 'label' => 'oro.contact.last_name.label'))
            ->add('gender', 'oro_gender', array('required' => true, 'label' => 'oro.contact.gender.label'));

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var Contact $contact */
                $contact = $event->getData();
                $this->defaultFirstContactDate($contact);
                $this->defaultSemContacted($contact);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'oro_contact';
    }

    /** @param Contact $contact */
    public function defaultFirstContactDate(Contact $contact)
    {
        if ($contact->getFirstContactDate() == null) {
            $contact->setFirstContactDate(new \DateTime('now'));
        }
    }

    /** @param Contact $contact */
    public function defaultSemContacted(Contact $contact)
    {
        // if the semester contacted is empty set the value from first contacted date
        if ($contact->getSemesterContacted() == null) {
            try {
                $contact->setSemesterContacted(
                    $this->container
                        ->get('academic_calendar')
                        ->getSemester($contact->getFirstContactDate())
                );
            } catch (\Exception $e) {
                $this->container->get('session')->getFlashBag()->add('error', $e->getMessage());
            }
        }
    }
}