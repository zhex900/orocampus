<?php

namespace CampusCRM\CampusCalendarBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;

class AcademicCalendarEventStartValidator extends ConstraintValidator
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
        // SystemCalendar events must start on Monday.
        if(!$rootContext->get('calendar')->getData() &&
            date('D', $value->getTimestamp()) != 'Mon'){
            $this->context->addViolation($constraint->message);
        }
    }
}
