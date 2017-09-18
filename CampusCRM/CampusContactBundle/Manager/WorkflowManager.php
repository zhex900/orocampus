<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 13/8/17
 * Time: 9:17 PM
 */

namespace CampusCRM\CampusContactBundle\Manager;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
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
            $result = preg_match('/' . $step . '/', $workflowitem->getCurrentStep()->getName());
            file_put_contents('/tmp/tag.log', 'current step:' . $step . $result . PHP_EOL, FILE_APPEND);
            return $result == 1;
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
        file_put_contents('/tmp/tag.log', 'Enter workflow' . PHP_EOL, FILE_APPEND);

        foreach ($workflowItems as $workflowItem) {
            file_put_contents('/tmp/tag.log', 'workflow: ' . $workflowItem->getWorkflowName() . PHP_EOL, FILE_APPEND);
            file_put_contents('/tmp/tag.log', 'workflow step: ' . $workflowItem->getCurrentStep()->getName() . PHP_EOL, FILE_APPEND);
            //find the follow-up workflow
            if (preg_match('/' . $workflow . '/', $workflowItem->getWorkflowName())) {
                file_put_contents('/tmp/tag.log', 'workflow Match!!!: ' . $workflowItem->getCurrentStep()->getName() . PHP_EOL, FILE_APPEND);
                return $workflowItem; //->getCurrentStep()->getName();
            }
        }
        return null;
    }

    /*
     * @param Contact $contact
     * @param string $workflow
     * @param string|Transition $transition
     */

    public function transitTo(Contact $contact, $workflow, $transition)
    {
        $workflowItem = $this->getCurrentWorkFlowItem($contact, $workflow);
        $this->transit($workflowItem, $transition);
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

    /*
     * @param Contact $contact
     * @param string $workflow
     */
    public function autoTransit(Contact $contact, $workflow)
    {
        /** @var WorkflowItem $workflowitem */
        $workflowitem = $this->getCurrentWorkFlowItem($contact, $workflow);

        if (!isset($workflowitem)) {
            return null;
        }

        $current_step = $workflowitem->getCurrentStep()->getName();
        file_put_contents('/tmp/tag.log', 'current step: ->' . $current_step. PHP_EOL, FILE_APPEND);

        switch ($current_step) {
            case 'unassigned';
                file_put_contents('/tmp/tag.log', 'current step: 2 ->' . $current_step. PHP_EOL, FILE_APPEND);

            case 'assigned';

        }
    }
}