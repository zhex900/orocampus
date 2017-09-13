<?php

namespace CampusCRM\CampusContactBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContactInfoCheckValidator extends ConstraintValidator
{
    /** @var  EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        if (!$value instanceof Contact) {
            throw new \InvalidArgumentException(sprintf(
                'Validator expects $value to be instance of "%s"',
                'Oro\Bundle\ContactBundle\Entity\Contact'
            ));
        }

        if (!$this->validateEmailorPhone($value)) {
            return;
        }
        if (!$this->validateUniqueEmail($value)) {
            return;
        }
        if (!$this->validateUniquePhone($value)) {
            return;
        }
    }

    /*
    * @param Contact $contact
    */
    private function validateEmailorPhone(Contact $contact){
        if ($contact->getEmails()->count() > 0 ||
            $contact->getPhones()->count() > 0) {
            return true;
        }
        $this->context->addViolation(
            'A contact must have either an emaill address or phone number.'
        );
        return false;
    }

    /*
     * @param Contact $contact
     */
    private function validateUniqueEmail(Contact $contact){

        $emails = $contact->getEmails();
        $id = $contact->getId();

        foreach ($emails as $email) {
            /** @var ContactEmail $result */
            $result = $this->em->getRepository('OroContactBundle:ContactEmail')
                ->findOneBy(array('email' => (string)$email));

            if ($result!==null) {
                if ((isset($id) && $id != $result->getOwner()->getId()) || (!isset($id))) {
                    $message = 'This email ' . $email . ' is already used by ' . $result->getOwner()->getFirstName() . ' ' . $result->getOwner()->getLastName() . ' ' . $result->getOwner()->getSemesterContacted();
                    $this->context->addViolation($message);
                    return false;
                }
            }
        }
        return true;
    }

    /*
     * @param Contact $contact
     */
    private function validateUniquePhone(Contact $contact){

        $phones = $contact->getPhones();
        $id = $contact->getId();

        foreach ($phones as $phone) {
            /** @var ContactEmail $result */
            $result = $this->em->getRepository('OroContactBundle:ContactPhone')
                ->findOneBy(array('phone' => (string)$phone));

            if ($result!==null) {
                if ((isset($id) && $id != $result->getOwner()->getId()) || (!isset($id))) {
                    $message = 'This phone number ' . $phone . ' is already used by ' . $result->getOwner()->getFirstName() . ' ' . $result->getOwner()->getLastName() . ' ' . $result->getOwner()->getSemesterContacted();
                    $this->context->addViolation($message);
                    return false;
                }
            }
        }
        return true;
    }
}
