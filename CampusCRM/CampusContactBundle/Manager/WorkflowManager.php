<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 13/8/17
 * Time: 9:17 PM
 */

namespace CampusCRM\CampusContactBundle\Manager;

use Monolog\Logger;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Model\WorkflowEntityConnector;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager as BaseManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\Tools\StartedWorkflowsBag;

class WorkflowManager extends BaseManager
{
    /** @var ContainerInterface */
    protected $container;

    /** @var String */
    protected $current_semester;

    /** @var \DateTime */
    protected $today;

    protected $teaching_week;

    /** @var Logger $logger */
    protected $logger;


    const CONTACT_FOLLOWUP = 'contact_followup';

    /**
     * @param WorkflowRegistry $workflowRegistry
     * @param DoctrineHelper $doctrineHelper
     * @param EventDispatcherInterface $eventDispatcher
     * @param WorkflowEntityConnector $entityConnector
     * @param StartedWorkflowsBag $startedWorkflowsBag
     */
    public function __construct(
        WorkflowRegistry $workflowRegistry,
        DoctrineHelper $doctrineHelper,
        EventDispatcherInterface $eventDispatcher,
        WorkflowEntityConnector $entityConnector,
        StartedWorkflowsBag $startedWorkflowsBag,
        ContainerInterface $container)
    {
        parent::__construct(
            $workflowRegistry,
            $doctrineHelper,
            $eventDispatcher,
            $entityConnector,
            $startedWorkflowsBag);

        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->current_semester = $this->container->get('academic_calendar')->getCurrentSemester();
        $this->today = $this->container->get('frequency_manager')->today();
        $this->teaching_week = $this->container->get('academic_calendar')->getTeachingWeek($this->today);

    }

    public function isUnassignedStep(Contact $contact)
    {
        return preg_match('/unassigned/', $this->getCurrentWorkFlowItem($contact, 'followup')->getCurrentStep()->getName());
    }

    /*
     * Returns true if the contact is currently at the workflow step
     * @param Contact $contact
     * @param string $workflow
     * @param string $step
     * @return bool
     */
    public function isCurrentlyAtStep(Contact $contact, $workflow, $step)
    {
        /** @var WorkflowItem $workflowitem */
        $workflowitem = $this->getCurrentWorkFlowItem($contact, $workflow);

        if (isset($workflowitem)) {
            $current_step = $workflowitem->getCurrentStep()->getName();
       //     $this->logger->debug('WorkflowManager->isCurrentlyAtStep: $current_step: ' . $current_step . ' compare with ' . $step);
            return strtolower($current_step) === strtolower($step);
        }

        return false;
    }

    /**
     * find the current step name of follow up workflow
     * @param Contact $contact
     * @param string $workflow
     * @return WorkflowItem
     */
    public function getCurrentWorkFlowItem(Contact $contact, $workflow)
    {
        $workflowItems = $this->getWorkflowItemsByEntity($contact);

        foreach ($workflowItems as $workflowItem) {
            //find the follow-up workflow
            if (preg_match('/' . $workflow . '/', $workflowItem->getWorkflowName())) {
                return $workflowItem; //->getCurrentStep()->getName();
            }
        }
        return null;
    }

    /*
     * Transit workflow from one step to another
     *
     * @param Contact $contact
     * @param string $workflow
     * @param string|Transition $from
     * @param string|Transition $to
     */

    public function transitFromTo(Contact $contact, $workflow, $from, $to)
    {
        if ($this->isCurrentlyAtStep($contact, $workflow, $from)) {
            /** @var WorkflowItem $workflowitem */
            $workflowitem = $this->getCurrentWorkFlowItem($contact, $workflow);
            $this->transit($workflowitem, $to);
        }
    }

    public function runTransitRulesForContactFollowup()
    {
        $this->startNoInitWorkflow(self::CONTACT_FOLLOWUP);
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'unassigned', 'assign');
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'assigned', 'contacted');
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'contacted', 'followup');
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'assigned', 'followup');
      //  $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'closed', 'reopen');
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'followup', 'stable');
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'stable', 'followup');
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP,'followup', 'rollover');
        $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP,'contacted', 'rollover');
    }

    public function runTransitRulesForContactFollowupByContact(Contact $contact)
    {
        // exit if workflow is not active
        if (!$this->isActiveWorkflow(self::CONTACT_FOLLOWUP)){
            return;
        }

        // exit if workflow have not start
        /** @var WorkflowItem $workflowitem */
        $workflowitem = $this->getCurrentWorkFlowItem($contact, self::CONTACT_FOLLOWUP);
        if (!isset($workflowitem) ){
            return;
        }

        $current_step = $workflowitem->getCurrentStep()->getName();

        if ( $current_step === 'unassigned' ){
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'unassigned', 'assign',$contact);
        }
        elseif ( $current_step === 'assigned' ){
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'assigned', 'contacted',$contact);
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'assigned', 'followup',$contact);
        }
        elseif ( $current_step ==='followup' ){
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'followup', 'stable',$contact);
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP,'followup', 'rollover',$contact);
        }
        elseif ( $current_step === 'contacted' ){
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'contacted', 'followup',$contact);
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP,'contacted', 'rollover',$contact);
        }
        elseif ( $current_step ==='stable' ){
            $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'stable', 'followup',$contact);
        }
        //  $this->applyTransitRuleFromTo(self::CONTACT_FOLLOWUP, 'closed', 'reopen');
    }

    /*
     * Helper function to apply auto transition rules to all contacts at
     * the source step.
     *
     * $from and $to are connected.
     *
     * This function looks for transit rule function name like this
     * transitRule'.$from.'To'.$to;
     *
     * @param string $from Source step name
     * @param string $to   Destination transition name
     * @param Contact $search_contact Apply rules one contact only
     */
    public function applyTransitRuleFromTo($workflow, $from, $to, Contact $search_contact=null)
    {
        $from = ucfirst(strtolower($from));
        $to = ucfirst(strtolower($to));
        $callback = 'transitRule' . $from . 'To' . $to;
        if (!method_exists($this, $callback)) {
            return;
        }

        if ($search_contact!=NULL){
            $contacts = [$search_contact];
        }else{
            // Get a list of all the contacts that are at step $from
            /** @var Contact[] $contacts */
            $contacts = $this
                ->container
                ->get('doctrine.orm.entity_manager')
                ->getRepository('OroContactBundle:Contact')
                ->findByWorkflowStep($workflow, $from);
        }

        $this->logger->debug('WorkflowManager. Apply transit rules ' . 'for workflow: ' . $workflow . ' '
            . $from . ' to ' . $to . '. contacts# ' . sizeof($contacts));

        foreach ($contacts as $contact) {
            $transit = 'NO';
            if (call_user_func_array(array(__CLASS__, $callback), array($contact, $from, $to))) {
                $this->transitFromTo($contact, $workflow, strtolower($from), strtolower($to));
                $contact->setLastReview($this->today);
                $transit = 'YES';
            }
            $this->logger->debug('WorkflowManager. Transit ' . $from . ' to ' . $to . ': ' . $transit . ' for contact ' . $contact->getFirstName() . ' ' . $contact->getLastName());
        }
    }

    /*
     * Transitions: Followup to Stable
     * Condition:   Satisfy stable criteria. For example:
     *              Meet 3 times over 5 weeks. Each meeting is
     *              5 days apart.
     *
     * Transitions: Stable to Followup
     * Condition:   Fails stable criteria.
     *
     * @param Contact $contact
     * @param string $from Source step name
     * @param string $to Destination transition name
     * @return bool
     */
    public function transitRuleFollowupAndStable(Contact $contact, $from, $to)
    {
        $events = $this
            ->container
            ->get('frequency_manager')->findAttendedEvents($contact, null, $this->current_semester);

        $freq = $this
            ->container
            ->get('frequency_manager')->findAttendanceFrequency($this->today, $this->teaching_week, $events, 0);

        $criteria = $this
            ->container
            ->get('frequency_manager')->getRegular();

        $this->logger->debug('WorkflowManager. Contact ' . $contact->getFirstName() . ' ' . $contact->getLastName().
        ' Freq: '.$freq);

        // Transit if the contact is having regular meeting
        return (
            ($from == 'Followup' && $to == 'Stable' && $criteria === $freq) ||
            ($from == 'Stable' && $to == 'Followup' && $criteria !== $freq)
        );
    }

    public function transitRuleFollowupToStable(Contact $contact, $from, $to)
    {
        return $this->transitRuleFollowupAndStable($contact, $from, $to);
    }

    public function transitRuleStableToFollowup(Contact $contact, $from, $to)
    {
        return $this->transitRuleFollowupAndStable($contact, $from, $to);
    }

    /*
     * Transition rule from unassigned to assign
     *
     * Rule: transit if the contact owner is a Full-timer
     *
     * @param Contact $contact
     * @param return bool
     */
    public function transitRuleUnassignedToAssign(Contact $contact)
    {
        return ($contact->getOwner() instanceof User &&
            $this->container->get('oro_user.manager')->hasRole($contact->getOwner(),'Full-timer'));
    }

    /*
    * Transitions: Assigned to Contacted
    * Condition:   At least one call within the current semester
    *
    * @param Contact $contact
    * @return bool
    */
    public function transitRuleAssignedToContacted(Contact $contact)
    {
        /** @var Call[] $calls */
        $calls = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('OroCallBundle:Call')
            ->findBy(['related_contact'=>$contact->getId()]);
        $calls = array_filter($calls, function ($call) {
            /** @var Call $call */
            return $this->current_semester ===
                $this->container->get('academic_calendar')->getSemester($call->getCallDateTime());
        });

        // Transit if there is at least one contact this semester
        return sizeof($calls)>0;
    }

    /*
     * Transitions: Closed to Reopen
     *
     * Condition:   Meet at least once after the closed date.
     *
     * @param Contact $contact
     * @return bool
     */

    public function transitRuleClosedToReopen(Contact $contact)
    {
        return $this->transitRuleMeetAtLastOnceAfterDate($contact,$contact->getLastReview());
    }

    public function transitRuleMeetAtLastOnceAfterDate(Contact $contact, \DateTime $date)
    {
        // get attended events this semester
        $events = $this
            ->container
            ->get('frequency_manager')->findAttendedEvents($contact, null, $this->current_semester);

        $events = array_column($events, 'date');

        // remove events after a given date
        $events = array_filter($events, function ($val) use ($date) {
            return $date < \DateTime::createFromFormat('Y-m-d', $val);
        });
        // Transit if there is at least one meeting left.
        return sizeof($events) > 0;
    }

    /*
     * Transitions: Assigned to Followup
     *
     * Condition:   Meet at least once this semester after the first contact date.
     *
     * @param Contact $contact
     * @return bool
     */
    public function transitRuleAssignedToFollowup(Contact $contact)
    {
        return $this->transitRuleMeetAtLastOnceAfterDate($contact,$contact->getFirstContactDate());
    }

    /*
     * Transitions: Contacted to Followup
     *
     * Condition:   Meet at least once this semester after the first contact date.
     *
     * @param Contact $contact
     * @return bool
     */
    public function transitRuleContactedToFollowup(Contact $contact)
    {
        return $this->transitRuleMeetAtLastOnceAfterDate($contact,$contact->getFirstContactDate());
    }

    /*
     * Transitions: Followup to Rollover
     *              Contacted to Rollover
     *
     * Condition:   Not a church kid AND
     *              Today        >= first date of next semester - 7 days
     *              AND contact first contact date is before first date of next semester - 7 days
     * Examples:    1 Jan 2017   >= 8 Feb 2017 - 7 days = 1 Feb (False)
     *              2 Feb 2017   >= 8 Feb 2017 - 7 days = 1 Feb (True)
     *
     * @param Contact $contact
     * @return bool
     */
    public function transitRuleRollover(Contact $contact)
    {
        if ($contact->getChurchKid()){
            return false;
        }
        /** @var \DateTime $next_semester */
        $next_semester_startdate = $this->container->get('academic_calendar')->getNextSemesterStartDate();
        $next_semester_startdate->modify('-7 day');

        return $this->today >= $next_semester_startdate &&
            $contact->getFirstContactDate() < $next_semester_startdate;
    }

    public function transitRuleFollowupToRollover(Contact $contact)
    {
        return $this->transitRuleRollover($contact);
    }

    public function transitRuleContactedToRollover(Contact $contact)
    {
        return $this->transitRuleRollover($contact);
    }

    /*
     * Start workflow for all the contacts have not started their workflow.
     * @param string workflow
     */
    public function startNoInitWorkflow($workflow)
    {
        $contacts = $this
            ->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroContactBundle:Contact')
            ->findByNotStartedWorkflow($workflow);

        $this->logger->debug('WorkflowManager. Try to start not started workflow: '
            . $workflow . '. Contacts#: ' . sizeof($contacts));

        foreach ($contacts as $contact) {
            $this->logger->debug('WorkflowManager. Start workflow: '
                . $workflow . ' for ' . $contact->getFirstName() . ' ' . $contact->getLastName());

            $this->activateWorkflow($workflow);
            $this->startWorkflow($workflow, $contact);
        }
    }
}