<?php

namespace CampusCRM\CampusCalendarBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AcademicCalendarEventStart extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Start date must be a Monday';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return  get_class($this) . 'Validator';
    }
}