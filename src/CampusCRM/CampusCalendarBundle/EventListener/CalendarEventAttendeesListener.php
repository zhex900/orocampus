<?php

namespace CampusCRM\CampusCalendarBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Oro\Bundle\CalendarBundle\EventListener\CalendarEventAttendeesListener as BaseListener;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CalendarEventAttendeesListener extends BaseListener
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
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (!$this->enabled) {
            return;
        }

        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $newEntities = $unitOfWork->getScheduledEntityInsertions();
        $updateEntities = $unitOfWork->getScheduledEntityUpdates();
        $deletedEntities = $unitOfWork->getScheduledEntityDeletions();

        foreach ($newEntities as $entity) {
            file_put_contents('/tmp/freq.log', '1 add attendee ' . PHP_EOL, FILE_APPEND);

            if ($entity instanceof Attendee) {
                file_put_contents('/tmp/freq.log', 'ADD: ' . $entity->getDisplayName() . PHP_EOL, FILE_APPEND);
                $this
                    ->container
                    ->get('frequency_manager')
                    ->updateAttendanceFrequency($entity);
            }

            if ($this->isAttendeeApplicable($entity, $unitOfWork)) {
                $this->updateCalendarEventUpdatedAt($entity->getCalendarEvent(), $unitOfWork);
            }
        }

        foreach ($updateEntities as $entity) {

            if ($this->isAttendeeApplicable($entity, $unitOfWork)) {
                $this->updateCalendarEventUpdatedAt($entity->getCalendarEvent(), $unitOfWork);
            }
        }

        foreach ($deletedEntities as $entity) {
            file_put_contents('/tmp/freq.log', '3 del attendee ' . PHP_EOL, FILE_APPEND);

            if ($entity instanceof Attendee) {
                file_put_contents('/tmp/freq.log', 'DEL: ' . $entity->getDisplayName() . PHP_EOL, FILE_APPEND);

                $this
                    ->container
                    ->get('frequency_manager')
                    ->updateAttendanceFrequency($entity, false);
            }

            if ($this->isAttendeeApplicable($entity, $unitOfWork)
                && !$unitOfWork->isScheduledForDelete($entity->getCalendarEvent())
            ) {
                $this->updateCalendarEventUpdatedAt($entity->getCalendarEvent(), $unitOfWork);
            }
        }
    }
}
