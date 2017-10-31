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

            if ($entity instanceof Attendee) {
                // remove duplicate inserts of attendee
                if ($this->attendeeExist($entity)) {
                    $unitOfWork->remove($entity);
                    continue;
                }

                $this
                    ->container
                    ->get('frequency_manager')
                    ->updateAttendanceFrequency($entity, 'ADD');
            }

            if ($this->isAttendeeApplicable($entity, $unitOfWork)) {
                $this->updateCalendarEventUpdatedAt($entity->getCalendarEvent(), $unitOfWork);
            }
        }

        foreach ($updateEntities as $entity) {

            if ($entity instanceof Attendee) {
                $this
                    ->container
                    ->get('frequency_manager')
                    ->updateAttendanceFrequency($entity, 'UPDATE');
            }

            if ($this->isAttendeeApplicable($entity, $unitOfWork)) {
                $this->updateCalendarEventUpdatedAt($entity->getCalendarEvent(), $unitOfWork);
            }
        }

        foreach ($deletedEntities as $entity) {

            if ($entity instanceof Attendee) {
                $this
                    ->container
                    ->get('frequency_manager')
                    ->updateAttendanceFrequency($entity, 'DELETE');
            }

            if ($this->isAttendeeApplicable($entity, $unitOfWork)
                && !$unitOfWork->isScheduledForDelete($entity->getCalendarEvent())
            ) {
                $this->updateCalendarEventUpdatedAt($entity->getCalendarEvent(), $unitOfWork);
            }
        }

        // update follow-up status
        $this->container
            ->get('campus_contact.workflow.manager')
            ->runTransitRulesForContactFollowup();
    }

    /**
     * @param Attendee $attendee
     * @return bool
     */
    private function attendeeExist(Attendee $attendee) {

        $attendees = $attendee->getCalendarEvent()->getAttendees();
        $i = 0;
        foreach ($attendees as $actualAttendee) {
            if ($attendee->getContact()->getId() == $actualAttendee->getContact()->getId()) {
                ++$i;
            }
        }
        return $i > 1;
    }
}
