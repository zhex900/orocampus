<?php

namespace CampusCRM\CampusContactBundle\Datagrid\Extension\MassAction;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\EntityMergeBundle\Doctrine\DoctrineHelper;
use Oro\Bundle\EntityMergeBundle\Exception\InvalidArgumentException;

//CampusCRM/CampusContactBundle/Datagrid/Extension/MassAction/AutoAllocateActionHandler.php
class AutoAllocateActionHandler implements MassActionHandlerInterface
{
    const SUCCESS_MESSAGE = 'oro.contact.autoallocate.mass_action.success';
    const ERROR_MESSAGE = 'oro.contact.autoallocate.mass_action.failure';
    const FOLLOWUP_WORKFLOW = 'followup';
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /** @var WorkflowManager */
    protected $workflowmanager;

    /** @var ContainerInterface */
    protected $container;

    /** @var TranslatorInterface  */
    protected $translator;

    /** @var string */
    protected $responseMessage = 'oro.grid.mass_action.delete.success_message';

    /**
     * @param WorkflowManager        $workflowmanager
     * @param ContainerInterface     $container
     * @param TranslatorInterface    $translator
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        WorkflowManager $workflowmanager,
        ContainerInterface $container,
        TranslatorInterface $translator,
        DoctrineHelper $doctrineHelper
    ) {
        $this->workflowmanager = $workflowmanager;
        $this->container = $container;
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
        $count = 0;
        $massAction = $args->getMassAction();
        $options = $massAction->getOptions()->toArray();

        if (empty($options['entity_name'])) {
            throw new InvalidArgumentException('Entity name is missing.');
        }

        $entityIdentifier = $this->doctrineHelper->getSingleIdentifierFieldName($options['entity_name']);
        $entityIds = $this->getIdsFromResult($args->getResults(), $entityIdentifier);

        $entities = $this->doctrineHelper->getEntitiesByIds(
            $options['entity_name'],
            $entityIds
        );

        foreach ($entities as $entity) {
            if ($entity instanceof Contact) {
                // if the current work flow step is unassigned.
                file_put_contents('/tmp/tag.log', 'handler:->' . $entity->getFirstName() . ' ' . $entity->getLastName() . PHP_EOL, FILE_APPEND);

                // auto allocate owner when follow-up workflow step is unassigned.
                if ($this->container
                    ->get('campus_contact.workflow.manager')
                    ->isUnassignedStep($entity)) {
                    file_put_contents('/tmp/tag.log', $entity->getFirstName() . ' ' . $entity->getLastName() . ' is unassigned' . PHP_EOL, FILE_APPEND);

                    $count++;
                    $this->container
                        ->get('oro_contact.auto_owner_allocator')
                        ->allocateUser($entity);

                    //move workflow step to assigned.
                    $this->container
                        ->get('campus_contact.workflow.manager')
                        ->transitTo($entity, self::FOLLOWUP_WORKFLOW, 'assign');

                    file_put_contents('/tmp/tag.log', $entity->getFirstName() . ' ' . $entity->getLastName() . ' flush' . PHP_EOL, FILE_APPEND);

                    $this->container->get('doctrine.orm.entity_manager')->flush();
                }
            }
        }

        file_put_contents('/tmp/tag.log', 'handler' . PHP_EOL, FILE_APPEND);

        return $this->generateResponse($count);
    }

    /**
     * @param int $count Processed entries
     *
     * @return MassActionResponse
     */
    protected function generateResponse($count)
    {
        if ($count > 0) {
            return new MassActionResponse(true, $this->translator->trans(self::SUCCESS_MESSAGE, ['%count%' => $count]));
        }

        return new MassActionResponse(false, $this->translator->trans(self::ERROR_MESSAGE, ['%count%' => $count]));
    }

    /**
     * @param IterableResultInterface $iterated
     * @param string $entityIdentifier
     * @return array
     */
    protected function getIdsFromResult(IterableResultInterface $iterated, $entityIdentifier)
    {
        $entityIds = array();
        /** @var ResultRecord $entity */
        foreach ($iterated as $entity) {
            $entityIds[] = $entity->getValue($entityIdentifier);
        }
        return $entityIds;
    }
}
