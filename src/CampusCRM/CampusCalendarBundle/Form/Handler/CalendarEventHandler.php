<?php

namespace CampusCRM\CampusCalendarBundle\Form\Handler;

use CampusCRM\CampusCalendarBundle\Manager\CalendarEventManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Form\Handler\CalendarEventHandler as BaseHandler;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\ContactBundle\Entity\Contact;

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
                $contexts = $this->syncActivityandContext($contexts, $attendees);

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

    /*
     * @param array $contexts
     * @param array $attendees
     *
     * @return array $contexts
     */
    private function syncActivityandContext($contexts,$attendees){

        // remove all user and contacts from contexts.
        $contexts = $this->clearContextUserContact($contexts);

        foreach ($attendees as $attendee) {

            if ($attendee->getUser()!== null){
                $contexts = array_merge([$attendee->getUser()],$contexts);
            }elseif($attendee->getContact()!==null){
                file_put_contents('/tmp/c.log',$attendee->getContact()->getFirstName(),FILE_APPEND);
                $contexts = array_merge([$attendee->getContact()],$contexts);
            }
        }

        foreach ($contexts as $c){
            if ($c instanceof User){
                file_put_contents('/tmp/c.log',$c->getFirstName(),FILE_APPEND);
            }
            elseif ($c instanceof Contact) {
                file_put_contents('/tmp/c.log',$c->getFirstName(),FILE_APPEND);
            }

        }
        return $contexts;
    }

    private function clearContextUserContact($contexts)
    {

        $new_contexts = [];
        foreach ($contexts as $context) {
            if (!($context instanceof User or $context instanceof Contact)) {
                $new_contexts = array_merge($new_contexts, [$context]);
            }
        }
        return $new_contexts;
    }
}
