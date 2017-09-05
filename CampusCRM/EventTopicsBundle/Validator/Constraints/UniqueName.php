<?php

namespace CampusCRM\EventTopicsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueName extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Event topic must be unique.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'uniquename.validator';
    }
}