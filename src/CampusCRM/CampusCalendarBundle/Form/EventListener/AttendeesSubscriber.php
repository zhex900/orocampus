<?php

namespace CampusCRM\CampusCalendarBundle\Form\EventListener;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\CalendarBundle\Entity\Attendee;

class AttendeesSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => ['fixSubmittedData', 100],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * Makes sure indexes of attendees from request are equal to indexes of the same
     * attendees so that in the end we end up with correct data.
     *
     * @param FormEvent $event
     */
    public function fixSubmittedData(FormEvent $event)
    {
        /** @var Attendee[]|Collection $data */
        $data      = $event->getData();
        $attendees = $event->getForm()->getData();
        if (!$attendees || !$data) {
            return;
        }

        $attendeeKeysByEmail = [];
        foreach ($attendees as $key => $attendee) {
            $id = $attendee->getEmail() ?: $attendee->getDisplayName();
            if (!$id) {
                return;
            }

            file_put_contents('/tmp/attendee.log','Display Name: '. $attendee->getDisplayName()
            . PHP_EOL, FILE_APPEND);

            $attendeeKeysByEmail[$id] = $key;
        }

        $nextNewKey = count($attendeeKeysByEmail);
        $fixedData = [];
        foreach ($data as $attendee) {
            if (empty($attendee['email']) && empty($attendee['displayName'])) {
                return;
            }

            $id = empty($attendee['email']) ? $attendee['displayName'] : $attendee['email'];

            $key = isset($attendeeKeysByEmail[$id])
                ? $attendeeKeysByEmail[$id]
                : $nextNewKey++;

            file_put_contents('/tmp/attendee.log','Display Name data: '. $attendee['displayName']
                . PHP_EOL, FILE_APPEND);

            file_put_contents('/tmp/attendee.log','key: '. $key
                . PHP_EOL, FILE_APPEND);

            $fixedData[$key] = $attendee;
        }

        file_put_contents('/tmp/attendee.log','$fixedData: '. print_r($fixedData,true). PHP_EOL,FILE_APPEND);

        $event->setData($fixedData);
    }
}
