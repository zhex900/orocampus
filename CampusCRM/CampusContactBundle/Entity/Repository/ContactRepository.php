<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 18/9/17
 * Time: 10:42 PM
 */

namespace CampusCRM\CampusContactBundle\Entity\Repository;

use Oro\Bundle\ContactBundle\Entity\Contact;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository as BaseRepository;

class ContactRepository extends BaseRepository
{
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
}
