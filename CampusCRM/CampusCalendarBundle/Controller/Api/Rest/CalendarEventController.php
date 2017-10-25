<?php

namespace CampusCRM\CampusCalendarBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\CalendarBundle\Controller\Api\Rest\CalendarEventController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;

/**
 * @RouteResource("calendarevent")
 * @NamePrefix("oro_api_")
 */
class CalendarEventController extends BaseController
{
    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('campus_calendar.calendar_event.form.handler.api');
    }

    /**
     * Add an exisitng contact to a calendar event as an attendee
     *
     * @param int $eventId Calendar event id
     * @param int $contactId Contact id
     *
     * @Put("/calendarevents/{eventId}/attendee/{contactId}",
     *     requirements={"eventId"="\d+", "contactId"="\d+"})
     *
     * @ApiDoc(
     *      description="Add an existing contact as an attendee to a Calendar Event",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_event_update")
     *
     * @return Response
     */
    public function putAddAttendeeAction($eventId, $contactId)
    {
        /*
         * Both event and contact have to exist and
         * Contact is not already an attendee
         */
        try {
            $attendee = $this->addContactToCalendarEvent($contactId,$eventId);
            $view = $this->view(['attendee_id' => $attendee->getId()], Codes::HTTP_OK);

        } catch (ForbiddenException $forbiddenEx) {
            $view = $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN);
        }
        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $eventId, 'entity' => $event]);
    }

    /*
     * Add contact to Calendar Event as an attendee
     *
     * @param int $contactId
     * @param int $eventId
     *
     * @return Attendee
     *
     * @throws ForbiddenException if contact is already an attendee.
     */
    protected function addContactToCalendarEvent($contactId, $eventId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var ActivityManager */
        $activityManager = $this->get('oro_activity.manager');

        /** @var CalendarEvent $event */
        $event = $em->getRepository('OroCalendarBundle:CalendarEvent')->find($eventId);
        /** @var Contact $contact */
        $contact = $em->getRepository('OroContactBundle:Contact')->find($contactId);
        $attendee = $this->getAttendeeByContact($event, $contact);

        /*
         * Both event and contact have to exist and
         * Contact is not already an attendee
         */

        if (isset($attendee)) {
            $reason = 'Contact is already attending this event';
            throw new ForbiddenException($reason);
        }
        $attendee = $this->createAttendee($contact);
        $event->addAttendee($attendee);
        $activityManager->addActivityTarget($event, $contact);
        $em->persist($attendee);
        $em->persist($event);
        $em->flush();

        return $attendee;
    }


    /**
     * Get attendee of Calendar Event by related User of Attendee.
     *
     * @param CalendarEvent $event
     * @param Contact $contact
     *
     * @return Attendee|null
     */
    protected function getAttendeeByContact(CalendarEvent $event, Contact $contact)
    {
        $attendees = $event->getAttendees();
        foreach ($attendees as $attendee) {
            if ($attendee->getContact() == $contact) {
                return $attendee;
            }
        }
        return null;
    }

    /**
     * @param Contact $contact
     *
     * @return Attendee
     */
    protected function createAttendee(Contact $contact)
    {
        $attendee = new Attendee();
        $attendee->setDisplayName($contact->getFirstName() . ' ' . $contact->getLastName());
        $attendee->setContact($contact);
        $attendee->setEmail($contact->getEmail());

        return $attendee;
    }
}
