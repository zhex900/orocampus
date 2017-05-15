<?php

namespace CampusCRM\CampusCalendarBundle\Form\Handler;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Form\Handler\CalendarEventHandler as BaseHandler;

class CalendarEventHandler extends BaseHandler
{

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
                if ($this->form->has('contexts')) {
                    $contexts = $this->form->get('contexts')->getData();
                    $owner = $entity->getCalendar() ? $entity->getCalendar()->getOwner() : null;
                    if ($owner && $owner->getId()) {
                        $contexts = array_merge($contexts, [$owner]);
                    }

                    // sync between attendees and contexts. One way sync only.
                    $attendees = $this->form->get('attendees')->getData();
                    $contexts = $this->syncActivityandContext($contexts, $attendees);

                    $this->activityManager->setActivityTargets($entity, $contexts);
                } elseif (!$entity->getId() && $entity->getRecurringEvent()) {
                    $this->activityManager->setActivityTargets(
                        $entity,
                        $entity->getRecurringEvent()->getActivityTargetEntities()
                    );
                }

                $this->processTargetEntity($entity, $request);

                $this->onSuccess($entity, $originalEntity);

                return true;
            }
        }

        return false;
    }

    private function syncActivityandContext($contexts,$attendees){

        foreach ($attendees as $attendee) {

            if ($attendee->getUser()!== null){
                $contexts = array_merge($contexts, [$attendee->getUser()]);
            }elseif($attendee->getContact()!==null){
                $contexts = array_merge($contexts, [$attendee->getContact()]);
            }
        }
        return $contexts;
    }
}
