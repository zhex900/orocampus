<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 13/8/17
 * Time: 9:17 PM
 */

namespace CampusCRM\CampusContactBundle\Manager;

use Oro\Bundle\ContactBundle\Entity\Contact;
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
       // $this->workflowmanager = $this->container->get('oro_workflow.manager');
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
        if (0 !== count($workflowItems)) {
            /** @var WorkflowItem $workflowItem */
            $workflowItem = array_shift($workflowItems);
            file_put_contents('/tmp/tag.log', 'workflow: ' . $workflowItem->getWorkflowName() . PHP_EOL, FILE_APPEND);
            file_put_contents('/tmp/tag.log', 'workflow step: ' . $workflowItem->getCurrentStep()->getName() . PHP_EOL, FILE_APPEND);
            //find the follow-up workflow
            if (preg_match('/'.$workflow.'/', $workflowItem->getWorkflowName())) {
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

    public function transitTo(Contact $contact, $workflow, $transition){
        $workflowItem = $this->getCurrentWorkFlowItem($contact, $workflow);
        $this->transit($workflowItem,$transition);
    }
}