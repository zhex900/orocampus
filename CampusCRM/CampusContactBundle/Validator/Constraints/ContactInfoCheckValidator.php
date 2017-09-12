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

        if (!$this->compulsoryFields($value)){
            return;
        }
        if (!$this->validateEmailorPhone($value)){
            return;
        }
        if (!$this->validateUniqueEmail($value)){
            return;
        }
        if (!$this->validateUniquePhone($value)){
            return;
        }
    }

    /*
     * return false if compulsory fields are not completed. Otherwise true.
     * @param Contact $contact
     * @return bool
     */
    private function compulsoryFields(Contact $contact){
        if ($contact->getFirstName()===null || strlen($contact->getFirstName())==0){
            $this->context->addViolation(
                'A contact must have a first name.'
            );
            return false;
        }
        if ($contact->getLastName()===null || strlen($contact->getLastName())==0){
            $this->context->addViolation(
                'A contact must have a last name.'
            );
            return false;
        }
        if ($contact->getGender()===null){
            $this->context->addViolation(
                'Gender cannot be blank.'
            );
            return false;
        }
        if ($contact->getContactSource()===null){
            $this->context->addViolation(
                'Contact source cannot be blank.'
            );
            return false;
        }
        if ($contact->getChurchKid()===null){
            $this->context->addViolation(
                'Church kid cannot be blank.'
            );
            return false;
        }
        return true;
    }

    /*
    * @param Contact $contact
    */
    private function validateEmailorPhone(Contact $contact){
        file_put_contents('/tmp/v.log', 'check email or contact');

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

            if ($result!==null &&
                 (isset($id) && $id != $result->getOwner()->getId()) ||
                (!isset($id))){
                $message = 'This email '. $email .' is already used by ' . $result->getOwner()->getFirstName(). ' '. $result->getOwner()->getLastName() . ' ' . $result->getOwner()->getSemesterContacted();
                $this->context->addViolation($message);
                return false;
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

            if ($result!==null &&
                (isset($id) && $id != $result->getOwner()->getId()) ||
                (!isset($id))){
                $message = 'This phone number '. $phone .' is already used by ' . $result->getOwner()->getFirstName(). ' '. $result->getOwner()->getLastName() . ' ' . $result->getOwner()->getSemesterContacted();
                $this->context->addViolation($message);
                return false;
            }
        }
        return true;
    }
}
