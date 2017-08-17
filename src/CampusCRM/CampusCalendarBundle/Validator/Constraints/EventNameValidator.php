<?php

namespace CampusCRM\CampusCalendarBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Oro\Bundle\ContactBundle\Entity\Contact;

class EventNameValidator extends ConstraintValidator
{
    /**
     * @param Contact $value
     * @param CalendarEvent $constraint
     * @throws \InvalidArgumentException
     *
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value == null) {
            $this->context->addViolation('You must select an event name.');
        }
        return;
    }
}
