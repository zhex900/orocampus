<?php

namespace CampusCRM\CampusCallBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CallTypeSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var ContainerInterface $container */
    protected $container;

    /**
     * @param EntityManager $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmitData'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmitData(FormEvent $event)
    {
        $data = $event->getData();
        $contact = $this->em->getRepository('OroContactBundle:Contact')->find($data['related_contact']);
        $contact->setAcContactCount($contact->getAcContactCount()+1);
        /** @var ContactPhone $phone */
        $phone = $contact->getPrimaryPhone();
        if (isset($phone)) {
            $phone = $phone->getPhone();
        } else {
            $phone = '123';
        }
        $data['phoneNumber'] = $phone;
        $data['contexts'] = json_encode(
            [
                'entityClass' => get_class($contact),
                'entityId' => $data['related_contact']
            ]
        );
        $event->setData($data);
        // if the contact is at assigned step, transit to contacted.
        $this
            ->container
            ->get('campus_contact.workflow.manager')
            ->transitFromTo($contact,'contact_followup', 'assigned','contacted');
    }
}