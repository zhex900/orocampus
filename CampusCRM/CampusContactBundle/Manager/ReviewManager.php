<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/9/17
 * Time: 9:30 PM
 */

namespace CampusCRM\CampusContactBundle\Manager;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\NotificationBundle\Entity\MassNotification;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReviewManager
{
    // key : step name
    // value: number of days until the next review
    const REVIEW_LIMIT = ['unassigned' => 1, 'assigned' => 14, 'contacted' => 14, 'followup'=>28];
    const SENDER = 'no-reply@orocampus.tk';
    const SUBJECT = 'Review reminder';

    /* @var \DateTIme */
    protected $today;

    /* @var ContainerInterface $container */
    protected $container;

    /* @var MassNotification[] $notifications */
    protected $notifications;

    /* @var User[] $users */
    protected $users;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->today = new \DateTime();
        $this->today->setTimezone(new \DateTimeZone('UTC'));
        $this->notifications = [];
        $this->users = $this->findUserByRole('FULL_TIMER');
    }

    public function applyReviewRulesForContactFollowUp(){
        $followup = $this->container->get('campus_contact.workflow.manager')::CONTACT_FOLLOWUP;
        $this->applyReviewRule($followup,'unassigned');
        $this->applyReviewRule($followup,'assigned');
        $this->applyReviewRule($followup,'contacted');
        $this->applyReviewRule($followup,'followup');
        $this->sendNotifications();
    }
    /*
    * Helper function to apply review to all contacts at
    * given step.
    *
    * @param string $at step name
    */
    public function applyReviewRule($workflow, $at)
    {
        if (!array_key_exists(strtolower($at), self::REVIEW_LIMIT)){
            return;
        }
        // Get a list of all the contacts that are at step $from
        /** @var Contact[] $contacts */
        $contacts = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroContactBundle:Contact')
            ->findByWorkflowStep($workflow, $at);

        $this->container->get('logger')
            ->debug('ReviewManager. Apply review rule ' . 'for workflow: ' . $workflow . ' at '
            . $at .'. contacts# ' . sizeof($contacts));

        foreach ($contacts as $contact) {
            $review = 'NO';
            // how many days since the last review?
            if (\date_diff($this->getLastReviewDate($contact),$this->today)->days >= self::REVIEW_LIMIT[$at]) {
                if (!$contact->getReviewRequest()) {
                    $contact->setReviewRequest(true);
                    $this->container->get('doctrine.orm.entity_manager')->flush($contact);
                }
                // add notification
                if ($at = 'unassigned'){
                    foreach ($this->users as $user)
                        $this->addNotification($contact,$at,$user->getEmail());
                }else{
                    $this->addNotification($contact,$at,$contact->getOwner()->getEmail());
                }
                $review = 'YES';
            }
            $this->container->get('logger')->debug('ReviewManager. Review ' . $at . ': '.$review.' for contact ' . $contact->getFirstName() . ' ' . $contact->getLastName());
        }
    }

    protected function getLastReviewDate(Contact $contact){
        return ($contact->getLastReview()==null? $contact->getFirstContactDate():$contact->getLastReview());
    }

    protected function addNotification(Contact $contact, $step, $email)
    {
        $i = $this->findNotificationByUserEmail($email);
        if ($i==-1){
            $notification[] = $this->createNotificationByContact($email);;
            $i = sizeof($this->notifications)-1;
        }
        $notification = &$this->notifications[$i];
        $reminder = $this->getReminderHeader($contact) . ' '. $this->getReminderMsg($contact,$step);
        if (sizeof($notification->getBody())==0){
            $notification->setBody($reminder);
        }
        $notification->setBody($reminder);
    }

    protected function getReminderHeader(Contact $contact)
    {
        return 'Hi '. $contact->getOwner()->getFirstName(). ',
        Please review the following contacts:
        ';
    }

    protected function getReminderMsg(Contact $contact, $step)
    {
        return $contact->getId() . ', '. $contact->getFirstName(). ', '.
            $contact->getLastName(). ', '. $contact->getSemesterContacted() .
            ', ' . $contact->getLastReview(). $step;
    }

    protected function createNotificationByContact($email)
    {
        $notification = new MassNotification();
        $notification->setEmail($email);
        $notification->setSender(self::SENDER);
        $notification->setSubject(self::SUBJECT);
        $notification->setProcessedAt($this->today);
        $notification->setScheduledAt($this->today);
        $notification->setStatus(1);
        return $notification;
    }

    /*
     * Return the index number of the notification if the already
     * exist with given email. return -1 if not find.
     *
     * @param string $email
     * @return int
     */
    protected function findNotificationByUserEmail($email){
        $i = -1;
        foreach ($this->notifications as $notification){
            $i++;
            if ($notification->getEmail()==$email){
                break;
            }
        }
        return $i;
    }

    protected function sendNotifications()
    {
        foreach ($this->notifications as $notification){
            $this->container->get('doctrine.orm.entity_manager')->persist($notification);
            $this->container->get('doctrine.orm.entity_manager')->flush();
        }
    }

    public function findUserByRole($role)
    {
        return $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('OroUserBundle:User')
            ->createQueryBuilder('u')
            ->select('u')
            ->join('u.roles','r')
            ->where('r.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->execute();
    }
}