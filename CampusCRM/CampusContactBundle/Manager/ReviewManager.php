<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/9/17
 * Time: 9:30 PM
 */

namespace CampusCRM\CampusContactBundle\Manager;

use Oro\Bundle\EmailBundle\Mailer\Processor as Mailer;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReviewManager
{
    /*
     * array ( step name => number of days until the next review)
     */
    const REVIEW_LIMIT = ['unassigned' => 1, 'assigned' => 7, 'contacted' => 7, 'followup'=>28];
    const SENDER = 'no-reply@orocampus.com.au';
    const SUBJECT = 'Review reminder';

    /* @var \DateTIme */
    protected $today;

    /* @var ContainerInterface $container */
    protected $container;

    /*
     * <code>
     * $emails = array (
     *      'email_address' => array( 'email', 'user', array(key=>value) )
     * )
     * </code>
     */
    protected $emails;

    /* @var User[] $ft_users */
    protected $ft_users;

    /* @var Mailer */
    protected $mailer;

    /* @var \Twig_Environment $twig */
    protected $twig;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->today = new \DateTime();
        $this->today->setTimezone(new \DateTimeZone('UTC'));
        $this->ft_users = $this->findUserByRole('FULL_TIMER');
        $this->mailer = $container->get('oro_email.mailer.processor');
        $this->emails=[];
        $this->twig = $container->get('twig');
    }

    public function applyReviewRulesForContactFollowUp(){
        $followup = $this->container->get('campus_contact.workflow.manager')::CONTACT_FOLLOWUP;
        $this->applyReviewRule($followup,'unassigned');
        $this->applyReviewRule($followup,'assigned');
        $this->applyReviewRule($followup,'contacted');
        $this->applyReviewRule($followup,'followup');
        $this->processEmail();
    }
    /*
    * Helper function to apply review to all contacts at
    * given step.
    *
    * @param string $workflow workflow name
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
                // send reminder email
                if ($at === 'unassigned'){
                    // notify all FT users
                    $this->sendEmails($contact,$at,$this->ft_users);
                }else{
                    $this->sendEmail($contact,$at,$contact->getOwner());
                }
                $review = 'YES';
            }
            $this->container->get('logger')->debug('ReviewManager. Review ' . $at . ': '.$review.' for contact ' . $contact->getFirstName() . ' ' . $contact->getLastName());
        }
    }

    protected function getLastReviewDate(Contact $contact){
        return ($contact->getLastReview()==null? $contact->getFirstContactDate():$contact->getLastReview());
    }

    /*
     * @param Contact $contact
     * @param string name of the workflow step
     * @param User $user. The user whom will receive the email.
     */
    protected function sendEmail(Contact $contact, $step, User $user)
    {
        if ($this->emails==null || !array_key_exists($user->getEmail(),$this->emails)){
            $this->emails[$user->getEmail()] = [$this->createEmail($user->getEmail()), $user,[]];
        }
        $this->emails[$user->getEmail()][2][]=$this->getReminderMsg($contact,$step,sizeof($this->emails[$user->getEmail()][2]));
    }

    protected function sendEmails(Contact $contact, $step, $users)
    {
        foreach ($users as $user)
        {
            $this->sendEmail($contact,$step,$user);
        }
    }

    protected function getReminderHeader(User $user)
    {
        return 'Hi '. $user->getFirstName(). ',
        Please review the following contacts:
        ';
    }

    /*
     * Compose the body of the reminder message
     * TODO:
     * Review link.
     *
     * @param Contact $contact
     * @param string $step
     * @param int $i
     * @return array. Key is the field name. Value is the field value.
     */
    protected function getReminderMsg(Contact $contact, $step, $i)
    {
        return [
            '#'                     => $i+1,
            'Name'                  => $contact->getFirstName(). ' '. $contact->getLastName(),
            'Semester contacted'    => $contact->getSemesterContacted(),
           // 'Last review'           => $contact->getLastReview()->format('d-m-Y'),
            'Last contacted date'   => $this->getLastContactDate($contact),
            'Status'                => $step
            ];
    }

    protected function getLastContactDate(Contact $contact){
        if ( $contact->getAcLastContactDate() == null ){
            return 'N/A';
        }else{
            /* @var \DateTime $last */
            $last = $contact->getAcLastContactDate();
            return $last->format('d-m-Y'). ', '. $last->diff($this->today)->format("%a"). ' days';
        }
    }

    protected function createEmail($to)
    {
        $email = $this->container->get('oro_email.email.model.builder')->createEmailModel();
        $email->setSubject('orocampus review');
        $email->setType('html');
        $email->setFrom('no-reply@orocampus.com.au');
        $email->setTo([$to]);
        return $email;
    }

    protected function processEmail()
    {
        foreach ($this->emails as $item){
            /* @var Email $email */
            $email = $item[0];
            /* @var User $user */
            $user = $item[1];
            // TODO:
            // Add the number of reviews required.
            $email->setBody($this->twig
                ->loadTemplate('@CampusContact/review_email.twig')
                ->render(['user' => $user->getFirstName(), 'reviews' => $item[2]]));
            $this->mailer->process($email);
            $this->container->get('logger')
                ->debug('ReviewManager. Review Send Email to '. $user->getFirstName(). ' s '.sizeof($item[2]));
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