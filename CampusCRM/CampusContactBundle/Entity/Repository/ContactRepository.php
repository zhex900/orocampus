<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 18/9/17
 * Time: 10:42 PM
 */

namespace CampusCRM\CampusContactBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository as BaseRepository;

class ContactRepository extends BaseRepository
{
    /*
     * Find all the contacts that are at a given workflow step.
     *
     * @param string $workflow Name of the workflow
     * @param string $step Name of the workflow step
     * @return Contact[]
     */
    public function findByWorkflowStep($workflow, $step)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->innerJoin('OroWorkflowBundle:WorkflowItem', 'wi', 'WITH','c.id = wi.entityId')
            ->innerJoin('wi.currentStep','step', Join::WITH, 'step.name = :step')
            ->innerJoin('wi.definition', 'workflowDefinition', Join::WITH, 'workflowDefinition.name = :workflow')
            ->setParameter('workflow', $workflow)
            ->setParameter('step',$step)
            ->getQuery()
            ->execute();
    }

    /*
     * Find all contacts that have not started their workflow.
     *
     * @param string $workflow Name of the workflow
     * @return Contact[]
     */
    public function findByNotStartedWorkflow($workflow)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('OroWorkflowBundle:WorkflowItem', 'wi',
                Join::WITH,'c.id = wi.entityId AND wi.workflowName = :workflow')
            ->where('wi.id is NULL')
            ->setParameter('workflow', $workflow)
            ->getQuery()
            ->execute();
    }
}
