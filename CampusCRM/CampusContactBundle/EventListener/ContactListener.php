<?php

namespace CampusCRM\CampusContactBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
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
     * @param ContainerInterface $container
     */
    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        parent::__construct($tokenStorage);
        $this->container = $container;
        $this->transit = false;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $newEntities = $unitOfWork->getScheduledEntityInsertions();

        foreach ($newEntities as $entity) {

            if ($entity instanceof Contact) {
                $entity->setStatus('Unassigned');
            }
        }
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
                $this->container
                    ->get('campus_contact.workflow.manager')
                    ->transitFromTo($entity, 'contact_followup', 'unassigned', 'assign');
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
            if ($args->hasChangedField('owner')) {
                file_put_contents('/tmp/tag.log', 'owner: null. transit' . PHP_EOL, FILE_APPEND);
                $this->transit = true;
            }
        }
    }

    /**
     * Run when Doctrine ORM metadata is loaded.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /**
         * @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
         */
        $classMetadata = $eventArgs->getClassMetadata();

        if (get_class(new contact()) === $classMetadata->getName()) {
            // Do whatever you want...
            $classMetadata->customRepositoryClassName = 'CampusCRM\CampusContactBundle\Entity\Repository\ContactRepository';
        }
    }
}
