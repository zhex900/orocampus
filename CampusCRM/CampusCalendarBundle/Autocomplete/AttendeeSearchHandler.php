<?php

namespace CampusCRM\CampusCalendarBundle\Autocomplete;

use Oro\Bundle\CalendarBundle\Autocomplete\AttendeeSearchHandler as BaseHandler;

class AttendeeSearchHandler extends BaseHandler
{
    /**
     * {@inheritdoc}
     */
    protected function getSearchAliases()
    {
        return ['oro_contact'];
    }
}
