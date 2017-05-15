<?php

namespace CampusCRM\CampusCalendarBundle\Manager\CalendarEvent;

use Oro\Bundle\CalendarBundle\Manager\CalendarEvent\UpdateAttendeeManager as BaseManager;
/**
 * Responsible to actualize attendees state after the event was created/updated:
 * - Bind attendees with users from $organization.
 * - Update related attendee of the event.
 * - Set default attendee status.
 * - Update attendees with empty display name.
 */
class UpdateAttendeeManager extends BaseManager
{
}
