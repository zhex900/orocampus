<?php

namespace CampusCRM\CampusCalendarBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\CalendarBundle\Manager\AttendeeRelationManager as BaseManager;

class AttendeeRelationManager extends BaseManager
{
    /**
     * Set related entity of the attendee.
     *
     * @param Attendee $attendee
     * @param object $relatedEntity
     *
     * @throws \InvalidArgumentException If related entity type is not supported.
     */
    public function setRelatedEntity(Attendee $attendee, $relatedEntity = null)
    {
        if ($relatedEntity instanceof User) {
            $attendee
                ->setUser($relatedEntity)
                //HACK: Append the contact id to the back of display name separated by #
                ->setDisplayName($this->nameFormatter->format($relatedEntity) . '   #' . $relatedEntity->getContact()->getId())
                ->setEmail($relatedEntity->getEmail());

        } elseif ($relatedEntity instanceof Contact) {

            $attendee
                ->setContact($relatedEntity)
                //HACK: Append the contact id to the back of display name separated by #
                //This is to add the contact entity to attendee.
                ->setDisplayName($this->nameFormatter->format($relatedEntity) . '   #' . $relatedEntity->getId())
                ->setEmail($relatedEntity->getEmail());

            // check if the contact have a linked user
            if ($relatedEntity->getUser() !== null) {
                $this->setRelatedEntity($attendee, $relatedEntity->getUser());
            }
            //$event->addActivityTarget($relatedEntity);
        } else {
            // Only User is supported as related entity of attendee.
            throw new \InvalidArgumentException(
                sprintf(
                    'Related entity must be an instance of "%s", "%s" is given.',
                    User::class,
                    is_object($relatedEntity) ? ClassUtils::getClass($relatedEntity) : gettype($relatedEntity)
                )
            );
        }
    }

    /*
     * @param array $contexts
     * @param array $attendees
	 *
     * @return array $contexts
    */
    public function syncActivityandContext($contexts, $attendees)
    {
        // remove all user and contacts from contexts.
        $contexts = $this->clearContextUserContact($contexts);

        foreach ($attendees as $attendee) {

            $this->setAttendeeContactRelationship($attendee);

            if ($attendee->getUser() !== null) {
                $contexts = array_merge([$attendee->getUser()], $contexts);
            } elseif ($attendee->getContact() !== null) {

                $contexts = array_merge([$attendee->getContact()], $contexts);
                // add user that is linked with a contact
                if ($attendee->getContact()->getUser() !== null) {
                    $contexts = array_merge([$attendee->getContact()->getUser()], $contexts);
                }
            }
        }
        return $contexts;
    }

    /*
     * @param Attendee $attendee
     */
    private function setAttendeeContactRelationship(Attendee $attendee)
    {
        preg_match('/(?P<email>.*)#(?P<contact_id>.*)/', $attendee->getDisplayName(), $matches);
        if (!empty($matches)) {
            if ($attendee->getContact() == null) {

                /** @var  Contact $contact */
                $contact = $this->registry
                    ->getRepository('OroContactBundle:Contact')
                    ->find($matches['contact_id']);

                $attendee->setContact($contact);
            }
        }
    }

    /*
     * @param Contact $contact
     * @param array $attendees
     * @return Attendee $attendee
     */
    public function findAttendeeByContact($contact, $attendees)
    {
        foreach ($attendees as $attendee) {
            if ($attendee->getContact() == $contact) {
                return $attendee;
            }
        }
    }

    protected function clearContextUserContact($contexts)
    {
        $new_contexts = [];
        if ($contexts != null) {
            foreach ($contexts as $context) {
                if (!($context instanceof User or $context instanceof Contact)) {
                    $new_contexts = array_merge($new_contexts, [$context]);
                }
            }
        }
        return $new_contexts;
    }
}
