<?php

namespace CampusCRM\CampusCalendarBundle\Form\Extension;

use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CalendarEventAttendeesSelectTypeExtension extends AbstractTypeExtension
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return 'oro_calendar_event_attendees_select';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$form->getParent()) {
            return;
        }

        $calendarEvent = $form->getParent()->getData();
        if (!$calendarEvent instanceof CalendarEvent) {
            return;
        }

        if ($calendarEvent->getId() !== null) {
            return;
        }
        file_put_contents('/tmp/log.log','build view'.PHP_EOL, FILE_APPEND);
        // add owner as default attendee when it is not system calendar
        if ($calendarEvent->getSystemCalendar() == null) {
            /** @var User $owner */
            $owner = $this->tokenStorage->getToken()->getUser();
            // if the user have a valid linked contact
            if ( $owner->getContact()!= null) {
                $this->addOwnerToAttendees($view, $owner);
            }
        }
    }

    /**
     * @param FormView $view
     * @param User    $owner
     */
    private function addOwnerToAttendees(FormView $view, User $owner)
    {
        $view->vars['value'] = json_encode([
            'entityClass' => User::class,
            'entityId'    => $owner->getId(),
        ]);

        $view->vars['attr']['data-selected-data'] = json_encode([
            'text' => $owner->getFullName(),
            'displayName' => $owner->getFullName(),
            'email' => $owner->getEmail(),
            'type' => null,
            'status' => null,
            'userId' => $owner->getId(),
            'id' => $view->vars['value'],
        ]);
    }
}
