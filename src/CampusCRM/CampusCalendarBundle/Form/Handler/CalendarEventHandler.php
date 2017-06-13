<?php

namespace CampusCRM\CampusCalendarBundle\Form\Handler;

use CampusCRM\CampusCalendarBundle\Manager\AttendeeRelationManager;
use CampusCRM\CampusCalendarBundle\Manager\CalendarEventManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Form\Handler\CalendarEventHandler as BaseHandler;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\ContactBundle\Entity\Contact;

class CalendarEventHandler extends BaseHandler
{
    /** @var  AttendeeRelationManager */
    private $attendee_relation_manager;

    /** @param AttendeeRelationManager $attendee_relation_manager */
    public function setAttendeeRelationManager(AttendeeRelationManager $attendee_relation_manager)
    {
        $this->attendee_relation_manager = $attendee_relation_manager;
    }

    /**
     * Process form
     *
     * @param  CalendarEvent $entity
     *
     * @return bool True on successful processing, false otherwise
     *
     * @throws AccessDeniedException
     * @throws \LogicException
     */
    public function process(CalendarEvent $entity)
    {
        $request = $this->getRequest();

        $this->checkPermission($entity);

        $this->form->setData($entity);

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            // clone entity to have original values later
            $originalEntity = clone $entity;

            $this->ensureCalendarSet($entity);

            $this->form->submit($request);

            if ($this->form->isValid()) {
                // TODO: should be refactored after finishing BAP-8722
                // Contexts handling should be moved to common for activities form handler
                $contexts=null;
                if ($this->form->has('contexts')) {
                    $contexts = $this->form->get('contexts')->getData();
                    $owner = $entity->getCalendar() ? $entity->getCalendar()->getOwner() : null;
                    if ($owner && $owner->getId()) {
                        $contexts = array_merge($contexts, [$owner]);
                    }

                } elseif (!$entity->getId() && $entity->getRecurringEvent()) {
                    $this->activityManager->setActivityTargets(
                        $entity,
                        $entity->getRecurringEvent()->getActivityTargetEntities()
                    );
                }

                $attendees = $entity->getAttendees();

                $contexts = $this->attendee_relation_manager->syncActivityandContext($contexts, $attendees);

                if ($contexts !== null){
                    $this->activityManager->setActivityTargets($entity, $contexts);
                }

                $this->processTargetEntity($entity, $request);

                $this->onSuccess($entity, $originalEntity);

                return true;
            }
        }

        return false;
    }
}
