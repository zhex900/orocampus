<?php

namespace CampusCRM\CampusContactBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContactInfoCheck extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return  get_class($this) . 'Validator';
    }
    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [
            static::CLASS_CONSTRAINT,
        ];
    }
}