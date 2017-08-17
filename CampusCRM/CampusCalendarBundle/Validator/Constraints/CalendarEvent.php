<?php

namespace CampusCRM\CampusCalendarBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class CalendarEvent extends Constraint
{
    /**
     * @var string
     */
    public $message = '';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return  get_class($this) . 'Validator';
    }
}