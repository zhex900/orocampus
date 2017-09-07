<?php

namespace CampusCRM\CampusCalendarBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AcademicCalendarEventEnd extends Constraint
{
    /**
     * @var string
     */
    public $message = 'End date must be a Sunday';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return  get_class($this) . 'Validator';
    }
}