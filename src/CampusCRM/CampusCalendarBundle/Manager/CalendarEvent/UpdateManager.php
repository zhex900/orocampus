<?php

namespace CampusCRM\CampusCalendarBundle\Manager\CalendarEvent;

use Oro\Bundle\CalendarBundle\Manager\CalendarEvent\UpdateManager as BaseManager;

/**
 * Responsible to actualize event state after it was updated.
 * - Actualize attendees state.
 * - Actualize child events state according to attendees.
 * - Actualize recurring calendar event exceptions state.
 */
class UpdateManager extends BaseManager
{
}
