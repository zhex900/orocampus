<?php

namespace CampusCRM\CampusCalendarBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

class AcademicCalendarEventEndValidator extends ConstraintValidator
{
    /**
     * @param \DateTime $value
     * @param CalendarEvent $constraint
     * @throws \InvalidArgumentException
     *
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $rootContext = $this->context->getRoot();
        // SystemCalendar events must end on Sunday.
        if(!$rootContext->get('calendar')->getData() &&
            date('D', $value->getTimestamp()) != 'Sun'){
            $this->context->addViolation($constraint->message);
        }
    }
}