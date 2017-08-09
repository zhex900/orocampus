<?php

namespace CampusCRM\CampusContactBundle\Manager;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\User;
use CampusCRM\CampusCalendarBundle\Provider\AcademicCalendar;
use Symfony\Component\DependencyInjection\Container;

class AutoOwnerAllocator
{
    const FT = 'Full-timer';
    const NONE_FT = 'None Full-timer';
    const NEW_ONE = 'New one';
    const CHURCH_KID = 'Church kid';
    const ADMIN = 'admin';

    /** @var TagManager */
    protected $tagManager;

    /** @var WorkflowManager */
    protected $workflowmanager;

    /** @var String */
    protected $current_semester;

    /** @var EntityManager */
    protected $em;

    /** @var  Container */
    private $container;

    /**
     * @param EntityManager $em
     * @param Container $container
     */
    public function __construct(EntityManager $em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->tagManager = $this->container->get('oro_tag.tag.manager');
        $this->workflowmanager = $this->container->get('oro_workflow.manager');
    }

    /**
     * @param Contact $contact
     * @return string
     */
    protected function getCurrentFollowupStep(Contact $contact){
        $workflowItems = $this->workflowmanager->getWorkflowItemsByEntity($contact);
        file_put_contents('/tmp/tag.log','Enter workflow'.PHP_EOL,FILE_APPEND);

        if (0 !== count($workflowItems)) {
            /** @var WorkflowItem $workflowItem */
            $workflowItem = array_shift($workflowItems);

            file_put_contents('/tmp/tag.log','workflow: '. $workflowItem->getWorkflowName().PHP_EOL,FILE_APPEND);
            file_put_contents('/tmp/tag.log','workflow step: '. $workflowItem->getCurrentStep()->getName().PHP_EOL,FILE_APPEND);

            //find the follow-up workflow
            if(preg_match('/follow_up/',$workflowItem->getWorkflowName())) {
                file_put_contents('/tmp/tag.log','workflow Match!!!: '. $workflowItem->getCurrentStep()->getName().PHP_EOL,FILE_APPEND);

                return $workflowItem->getCurrentStep()->getName();
            }
        }
    }

    /**
     * @param String $tagName
     * @param Collection $tags
     * @return boolean
     */
    protected function findTag($tagName, Collection $tags){
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            if ($tag->getName() == $tagName) {
                return true;
            }
        }
        return false;
    }


    /**
     * {@inheritdoc}
     * @param Contact $contact
     */
    public function allocateUser(Contact $contact)
    {

        //Auto allocate owner when auto allocate field is true and the contact is
        //either a New one or Church kid. AND step= unassigned
        $tags = $this->tagManager->getTags($contact);
        $step = $this->getCurrentFollowupStep($contact);

        if ( $contact->getAutoAllocate() && preg_match('/unassigned/',$step) &&
            ($this->findTag(self::NEW_ONE, $tags) ||
                $this->findTag(self::CHURCH_KID, $tags))
        ) {
            file_put_contents('/tmp/tag.log', 'd-auto' . PHP_EOL, FILE_APPEND);

            $this->allocateUser($contact);


            $assigned_user = $this->findAssignedUser($contact->getGender());
            $owner_user = $this->findOwnerUser($contact->getGender());
            $owner_id = $owner = $assigned = null;
            file_put_contents('/tmp/tag.log', '$owner_user:' . $owner_user['id'] . PHP_EOL, FILE_APPEND);

            if (!empty($owner_user)) {
                $owner_id = $owner_user['id'];
                /** @var User $owner */
                $owner = $this->em->getRepository('OroUserBundle:User')->findUsersByIds(array($owner_id))[0];
                file_put_contents('/tmp/tag.log', '$owner' . $owner->getUsername() . PHP_EOL, FILE_APPEND);

            }

            if (!empty($assigned_user)) {
                // the first user should be the assigned user.
                $assigned_id = $assigned_user['id'];

                if ($assigned_user['group_name'] == self::FT && $owner_id != null) {
                    // allocate to the user with least number of owned contacts.
                    $assigned_id = $owner_id;
                }
                /** @var User $assigned */
                $assigned = $this->em->getRepository('OroUserBundle:User')->findUsersByIds(array($assigned_id))[0];
            }
            /*
                    if ($assigned == null or $owner == null) {

                        $admin = ($this->em->getRepository('OroUserBundle:User')->findUsersByUsernames(array(self::ADMIN)))[0];

                        if ($assigned == null) {
                            $assigned = $admin;
                        }
                        if ($owner == null) {
                            $owner = $admin;
                        }
                    }*/
            /** @var User $admin */
            file_put_contents('/tmp/tag.log', '$assigned' . $assigned->getUsername() . PHP_EOL, FILE_APPEND);

            $contact->setAssignedTo($assigned);
            $contact->setOwner($owner);

            $unitOfWork = $this->em->getUnitOfWork();
            $unitOfWork->recomputeSingleEntityChangeSet(
                $this->em->getClassMetadata(Contact::class),
                $contact
            );
        }
    }



   /**
    * Find an user that
    * @param String $gender
    * @return array
    */
    private function findAssignedUser($gender)
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
                      INNER JOIN oro_tag_tagging AS tag ON tag.record_id=cc.id AND tag.entity_name LIKE "%ContactBundle%"
                      INNER JOIN oro_tag_tag AS tt ON tag.tag_id = tt.id
                      WHERE  tt.name= :contact_group AND cc.semester_contacted= :contact_sem
                    ) AS c ON u.id=c.assigned_to_user_id 
                WHERE ( a_g.name= :FT  OR a_g.name = :None_FT ) 
                AND u.enabled = 1 AND u.username != :admin AND u.gender_id = :gender
                GROUP BY u.id, group_name
                ORDER BY num_contact ASC 
                LIMIT 1
                ';

        $stmt = $connection->prepare($assigned_sql);

        file_put_contents('/tmp/tag.log', 'sql'.PHP_EOL, FILE_APPEND);

        $stmt->execute(array('contact_group'=>self::NEW_ONE,
            'contact_sem'=>$this->current_semester,
            'FT'=>self::FT,
            'None_FT'=>self::NONE_FT,
            'admin'=>self::ADMIN,
            'gender'=>$gender));

        return $stmt->fetch();
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
                SELECT count(c.user_owner_id) as num_contact, u.id, a_g.name as group_name, u.username, u.first_name
                FROM `oro_user` as u 
                INNER JOIN `oro_user_access_group` as u_g on u.id=u_g.user_id 
                INNER join `oro_access_group` as a_g on u_g.group_id=a_g.id 
                LEFT JOIN 
                    ( SELECT cc.user_owner_id
                      FROM orocrm_contact AS cc 
                      INNER JOIN oro_tag_tagging AS tag ON tag.record_id=cc.id AND tag.entity_name LIKE "%ContactBundle%"
                      INNER JOIN oro_tag_tag AS tt ON tag.tag_id = tt.id
                      WHERE  tt.name= :contact_group AND cc.semester_contacted= :contact_sem
                    ) AS c ON u.id=c.user_owner_id
                WHERE a_g.name= :FT AND u.enabled = 1 AND u.username != :admin AND u.gender_id = :gender
                GROUP BY u.id, group_name 
                ORDER BY num_contact ASC 
                LIMIT 1
                ';

        $stmt = $connection->prepare($owner_sql);
        $stmt->execute(array('contact_group'=>self::NEW_ONE,
            'contact_sem'=>$this->current_semester,
            'FT'=>self::FT,
            'admin'=>self::ADMIN,
            'gender'=>$gender));

        return $stmt->fetch();
    }
}