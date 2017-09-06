<?php

namespace CampusCRM\CampusCalendarBundle\Form\Handler;

use Oro\Bundle\CalendarBundle\Manager\AttendeeRelationManager;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Form\Handler\CalendarEventApiHandler as BaseHandler;

class CalendarEventApiHandler extends BaseHandler
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
     * @return bool  True on successful processing, false otherwise
     */
    public function process(CalendarEvent $entity)
    {
        $request = $this->getRequest();

        $this->form->setData($entity);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            // clone entity to have original values later
            $originalEntity = clone $entity;

            $this->form->submit($request->request->all());

            if ($this->form->isValid()) {
                // TODO: should be refactored after finishing BAP-8722
                // Contexts handling should be moved to common for activities form handler
                $contexts = null;
                if ($this->form->has('contexts') && $request->request->has('contexts')) {
                    $contexts = $this->form->get('contexts')->getData();
                    $owner = $entity->getCalendar() ? $entity->getCalendar()->getOwner() : null;
                    if ($owner && $owner->getId()) {
                        $contexts = array_merge($contexts, [$owner]);
                    }
                    // $this->activityManager->setActivityTargets($entity, $contexts);
                } elseif (!$entity->getId() && $entity->getRecurringEvent()) {
                    $this->activityManager->setActivityTargets(
                        $entity,
                        $entity->getRecurringEvent()->getActivityTargets()
                    );
                }

                $attendees = $entity->getAttendees();
                $contexts = $this->attendee_relation_manager->syncActivityandContext($contexts, $attendees);

                if ($contexts !== null) {
                    $this->activityManager->setActivityTargets($entity, $contexts);
                }

                $this->onSuccess($entity, $originalEntity);

                return true;
            }
        }

        return false;
    }
}
