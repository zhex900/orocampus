<?php

namespace CampusCRM\CampusContactBundle\Form\Extension;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\DependencyInjection\Container;

class CampusContactTypeExtension extends AbstractTypeExtension
{
    /** @var String */
    protected $current_semester;

    /** @var  ContainerInterface */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        try {
            $this->current_semester = $this->container
                ->get('academic_calendar')
                ->getCurrentSemester();
        }catch (\Exception $e) {
            $this->container->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
        if($contact->getFirstContactDate()==null){
            $contact->setFirstContactDate(new \DateTime('now'));
        }
    }

    /** @param Contact $contact */
    public function defaultSemContacted(Contact $contact)
    {
        // if the semester contacted is empty set the value from first contacted date
        if($contact->getSemesterContacted()==null){

            $contact->setSemesterContacted(
                $this->container
                    ->get('academic_calendar')
                    ->getSemester($contact->getFirstContactDate())
            );
        }
    }
}