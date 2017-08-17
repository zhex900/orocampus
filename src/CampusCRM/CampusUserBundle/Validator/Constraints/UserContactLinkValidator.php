<?php

namespace CampusCRM\CampusUserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class UserContactLinkValidator extends ConstraintValidator
{
    /**
     * @param Contact $value
     * @param UserContactLink $constraint
     * @throws \InvalidArgumentException
     *
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {

        if ($value == null) {
            $this->context->addViolation('You must select at list one contact!');
        }
        // Contact already linked to an user.
        elseif ($value->getUser() != null) {

            $root = $this->context->getRoot();
            // Get the current user id
            if ($root instanceof FormInterface) {
                $user_id = $root->has('id') ? $root->get('id')->getData() : false;
            } else {
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $user_id = $propertyAccessor->getValue($root, 'id');
            }

            if ($value->getUser()->getId() != $user_id) {
                $this->context->addViolation($constraint->message);
            }
            // Linked contact should have the same first and last name as the user.
            elseif ($value->getFirstName() != $value->getUser()->getFirstName() ||
                $value->getLastName() != $value->getUser()->getLastName()) {
                //return;
                $this->context->addViolation('Names of contact and user does not match.');
            } else {return; }
        } else {
            return;
        }
    }
}
