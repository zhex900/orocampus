<?php

namespace CampusCRM\CampusUserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UserContactLink extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Linked contact must be have the same name as the user.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return  get_class($this) . 'Validator';
    }
}