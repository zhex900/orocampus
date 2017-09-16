<?php

namespace CampusCRM\CampusCallBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CallTypeSubscriber implements EventSubscriberInterface
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'preSubmitData'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmitData(FormEvent $event)
    {
        $data = $event->getData();
        $contact = $this->em->getRepository('OroContactBundle:Contact')->find($data['related_contact']);
        $contact->setAcContactCount(1);
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
    }
}