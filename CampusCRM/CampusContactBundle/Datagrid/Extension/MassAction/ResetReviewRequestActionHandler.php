<?php

namespace CampusCRM\CampusContactBundle\Datagrid\Extension\MassAction;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\EntityMergeBundle\Doctrine\DoctrineHelper;
use Oro\Bundle\EntityMergeBundle\Exception\InvalidArgumentException;

class ResetReviewRequestActionHandler implements MassActionHandlerInterface
{
    const SUCCESS_MESSAGE = 'oro.contact.resetreviewrequest.mass_action.success';
    const ERROR_MESSAGE = 'oro.contact.resetreviewrequest.mass_action.failure';

    /** @var TranslatorInterface  */
    protected $translator;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var EntityManager */
    private $em;

    /**
     * @param TranslatorInterface $translator
     * @param DoctrineHelper $doctrineHelper
     * @param EntityManager $em
     */
    public function __construct(TranslatorInterface $translator,
                                DoctrineHelper $doctrineHelper,
                                EntityManager $em) {
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
        $this->em = $em;
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
                $count++;
                $entity->setReviewRequest(1);
            }
        }

        if ($count>0) { $this->em->flush();}

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
