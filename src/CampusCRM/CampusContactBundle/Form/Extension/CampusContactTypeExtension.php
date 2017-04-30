<?php

namespace CampusCRM\CampusContactBundle\Form\Extension;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;

class CampusContactTypeExtension extends AbstractTypeExtension
{
    const FT = 'Full-timer';
    const NONE_FT = 'None Full-timer';
    const NEW_ONE = 'New One';
    const ADMIN = 'admin';

    /** @var String */
    protected $current_semester;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->current_semester='2017A';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var Contact $contact */
                $contact = $event->getData();
                $this->defaultFirstContactDate($contact);
                $this->defaultSemContacted($contact);
                if ( $contact->getOwner()->getUsername() == self::ADMIN )
                {
                    $this->allocateUser($contact);
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'oro_contact';
    }

    /** @param Contact $contact */
    public function defaultFirstContactDate(Contact $contact)
    {
        if($contact->getFirstContactDate()==null){
            $contact->setFirstContactDate(new \DateTime('now'));
        }
    }

    /** @param Contact $contact */
    public function defaultSemContacted(Contact $contact)
    {
        // if the semester contacted is empty set the value from first contacted date
        if($contact->getSemesterContacted()==null){
            $date = $contact->getFirstContactDate();
            $sem = $date->format("Y") . $this->getSemester($date);
            $contact->setSemesterContacted($sem);
        }
    }

    /**
     * {@inheritdoc}
     * @param Contact $contact
     */
    public function allocateUser(Contact $contact)
    {
        $assigned_user = $this->findAssignedUser();
        $owner_id = $this->findOwnerUser()['id'];

        // the first user should be the assigned user.
        $assigned_id = $assigned_user['id'];

        if ( $assigned_user['group_name'] == self::FT)
        {
            // allocate to the user with least number of owned contacts.
            $assigned_id=$owner_id;
        }

        /** @var User $assigned */
        $assigned = ($this->em->getRepository('OroUserBundle:User')->findUsersByIds(array($assigned_id)))[0];
        /** @var User $owner */
        $owner = ($this->em->getRepository('OroUserBundle:User')->findUsersByIds(array($owner_id)))[0];

        if ($assigned != null  && $owner != null)
        {
            $contact->setAssignedTo($assigned);
            $contact->setOwner($owner);
        }
    }

    /** @param \DateTime $dateTime */
    private function getSemester(\DateTime $dateTime){
        $break = new \DateTime('2017-07-01');
        if( $dateTime < $break){
            return 'A';
        }
        else {
            return 'B';
        }
    }

    private function findAssignedUser()
    {
        $connection = $this->em->getConnection();
        /*
         * This raw SQL query returns a list of users with the number of NEW ONE contacts
         */
        $assigned_sql = '
                SELECT u.id, a_g.name as group_name, u.username, u.first_name, count(c.assigned_to_user_id) as num_contact
                FROM `oro_user` as u 
                INNER JOIN `oro_user_access_group` as u_g on u.id=u_g.user_id 
                INNER join `oro_access_group` as a_g on u_g.group_id=a_g.id 
                LEFT JOIN 
                    ( SELECT cc.assigned_to_user_id
                      FROM orocrm_contact AS cc 
                      INNER JOIN orocrm_contact_to_contact_grp AS cc_g ON cc_g.contact_id=cc.id 
                      INNER join orocrm_contact_group AS c_g ON cc_g.contact_group_id=c_g.id  
                      WHERE  c_g.label= :contact_group AND cc.semester_contacted= :contact_sem
                    ) AS c ON u.id=c.assigned_to_user_id 
                WHERE ( a_g.name= :FT  OR a_g.name = :None_FT ) AND u.enabled = 1 AND u.username != :admin
                GROUP BY u.id 
                ORDER BY num_contact, group_name DESC 
                LIMIT 1
                ';

        $stmt = $connection->prepare($assigned_sql);
        $stmt->execute(array('contact_group'=>self::NEW_ONE,
            'contact_sem'=>$this->current_semester,
            'FT'=>self::FT,
            'None_FT'=>self::NONE_FT,
            'admin'=>self::ADMIN));
        return $stmt->fetch();
    }

    private function findOwnerUser()
    {
        $connection = $this->em->getConnection();
        /*
         * This raw SQL query returns a list of users with the number of NEW ONE contacts
         */
        $owner_sql = '
                SELECT u.id, a_g.name as group_name, u.username, u.first_name, count(c.user_owner_id) as num_contact
                FROM `oro_user` as u 
                INNER JOIN `oro_user_access_group` as u_g on u.id=u_g.user_id 
                INNER join `oro_access_group` as a_g on u_g.group_id=a_g.id 
                LEFT JOIN 
                    ( SELECT cc.user_owner_id
                      FROM orocrm_contact AS cc 
                      INNER JOIN orocrm_contact_to_contact_grp AS cc_g ON cc_g.contact_id=cc.id 
                      INNER join orocrm_contact_group AS c_g ON cc_g.contact_group_id=c_g.id  
                      WHERE  c_g.label= :contact_group AND cc.semester_contacted= :contact_sem
                    ) AS c ON u.id=c.user_owner_id
                WHERE a_g.name= :FT AND u.enabled = 1 AND u.username != :admin
                GROUP BY u.id 
                ORDER BY num_contact, group_name DESC 
                LIMIT 1
                ';

        $stmt = $connection->prepare($owner_sql);
        $stmt->execute(array('contact_group'=>self::NEW_ONE,
            'contact_sem'=>$this->current_semester,
            'FT'=>self::FT,
            'admin'=>self::ADMIN));

        return $stmt->fetch();
    }
}