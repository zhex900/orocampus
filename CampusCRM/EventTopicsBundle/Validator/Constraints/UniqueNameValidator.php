<?php

namespace CampusCRM\EventTopicsBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNameValidator extends ConstraintValidator
{
    /** @var  EntityManager */
    protected $em;

    /**
     * {@inheritDoc}
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $value
     * @param UniqueName $constraint
     * @throws \InvalidArgumentException
     *
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $result = $this->em->getRepository('EventTopicsBundle:EventTopics')->findOneBy(['name' => $value]);

        if ( $result != null ){
            $this->context->addViolation($constraint->message);
        }
        else{
            return;
        }
    }
}
