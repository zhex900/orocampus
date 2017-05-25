<?php

namespace CampusCRM\CampusCalendarBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
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
                ->setDisplayName($this->nameFormatter->format($relatedEntity))
                ->setEmail($relatedEntity->getEmail());
           // $event->addActivityTarget($relatedEntity);
        } elseif ($relatedEntity instanceof Contact) {
            $attendee
                ->setContact($relatedEntity)
                ->setDisplayName($this->nameFormatter->format($relatedEntity))
                ->setEmail($relatedEntity->getEmail());

            // check if the contact have a linked user
            if( $relatedEntity->getUser() !== null ){
                $this->setRelatedEntity($attendee,$relatedEntity->getUser());
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
}
