<?php

namespace CampusCRM\EventNameBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueName extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Event name must be unique.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'event_name.uniquename.validator';
    }
}