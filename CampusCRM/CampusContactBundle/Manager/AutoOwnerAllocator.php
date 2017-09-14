<?php

namespace CampusCRM\CampusContactBundle\Manager;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AutoOwnerAllocator
{
    const FT = 'Full-timer';
    const NONE_FT = 'None Full-timer';
    const CHURCH_KID = 'Church kid';
    const ADMIN = 'admin';

    /** @var String */
    protected $current_semester;

    /** @var EntityManager */
    protected $em;

    /** @var  ContainerInterface */
    private $container;

    /**
     * @param EntityManager $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->current_semester = $this->container
            ->get('academic_calendar')
            ->getCurrentSemester();
    }

    /**
     * {@inheritdoc}
     * @param Contact $contact
     * @return User
     */
    public function allocateUser(Contact $contact)
    {
        file_put_contents('/tmp/tag.log', 'd-auto' . PHP_EOL, FILE_APPEND);
        $owner_user = $this->findOwnerUser($contact->getGender());
        $owner = null;
        file_put_contents('/tmp/tag.log', '$owner_user:' . $owner_user['id'] . PHP_EOL, FILE_APPEND);

        if (!empty($owner_user)) {
            $owner_id = $owner_user['id'];
            /** @var User $owner */
            $owner = $this->em->getRepository('OroUserBundle:User')->findUsersByIds(array($owner_id))[0];
            file_put_contents('/tmp/tag.log', '$owner' . $owner->getUsername() . PHP_EOL, FILE_APPEND);
        }

        /** @var User $admin */
        file_put_contents('/tmp/tag.log', '$owner: ' . $owner->getUsername() . PHP_EOL, FILE_APPEND);

        $contact->setOwner($owner);
        return $owner;

    }

    /**
     * Find an user that
     * @param String $gender
     * @return array
     */
    private function findOwnerUser($gender)
    {
        $connection = $this->em->getConnection();
        /*
         * This raw SQL query returns a list of users with the number of NEW ONE contacts
         */
        $owner_sql = '
                SELECT count(c.user_owner_id) as num_contact, u.id, a_g.label as role_name, u.username, u.first_name
                FROM `oro_user` as u 
                INNER JOIN `oro_user_access_role` as u_g on u.id=u_g.user_id 
                INNER join `oro_access_role` as a_g on u_g.role_id=a_g.id 
                LEFT JOIN 
                    ( SELECT cc.user_owner_id
                      FROM orocrm_contact AS cc 
                      WHERE  (cc.church_kid!=1 OR ISNULL(cc.church_kid)) AND cc.semester_contacted= :contact_sem
                    ) AS c ON u.id=c.user_owner_id
                WHERE a_g.label= :FT AND u.enabled = 1 AND u.username != :admin AND u.gender_id = :gender
                GROUP BY u.id, role_name 
                ORDER BY num_contact ASC 
                ';

        $stmt = $connection->prepare($owner_sql);
        $stmt->execute(array(
            'contact_sem' => $this->current_semester,
            'FT' => self::FT,
            'admin' => self::ADMIN,
            'gender' => $gender));
        $array = $stmt->fetchAll();
        file_put_contents('/tmp/tag.log', 'findOwnerUser sql ' . print_r($array, true) . PHP_EOL, FILE_APPEND);
        return $array[0];
    }
}