<?php

namespace CampusCRM\CampusContactBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\ContactBundle\EventListener\ContactListener as BaseListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;

class ContactListener extends BaseListener
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ContainerInterface */
    protected $container;

    /** @boolean $transit */
    protected $transit;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        parent::__construct($tokenStorage);
        $this->container = $container;
        $this->transit = false;
    }

    /**
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($this->transit) {
            if ($entity instanceof Contact) {
                $this->transitToAssigned($entity);
            }
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        parent::preUpdate($args);

        $entity = $args->getEntity();

        if ($entity instanceof Contact) {
            file_put_contents('/tmp/tag.log', 'preUpdate: ' . print_r(array_keys($args->getEntityChangeSet()), true) . PHP_EOL, FILE_APPEND);

            if ($args->hasChangedField('assignedTo')
                && $args->getOldValue('assignedTo') == null
                && $args->getNewValue('assignedTo') != null
            ) {
                file_put_contents('/tmp/tag.log', '$assignedTo: null. transit' . PHP_EOL, FILE_APPEND);
                $this->transit = true;
            }
        }
    }

    /**
     * @param Contact $contact
     */
    private function transitToAssigned(Contact $contact)
    {
        file_put_contents('/tmp/tag.log', 'transitToAssigned: ' . $contact->getFirstName() . ' ' . $contact->getLastName() . PHP_EOL, FILE_APPEND);

        // When follow-up workflow step is unassigned.
        if ($current_step = $this->container
            ->get('campus_contact.workflow.manager')
            ->isUnassignedStep($contact)
        ) {

            //move workflow step to assigned.
            $this->container
                ->get('campus_contact.workflow.manager')
                ->transitTo($contact, 'followup', 'assign');
            file_put_contents('/tmp/tag.log', 'set to assigned: ' . $contact->getFirstName() . ' ' . $contact->getLastName() . PHP_EOL, FILE_APPEND);
        }
    }
}
