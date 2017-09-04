<?php

namespace CampusCRM\EventTopicsBundle\Controller\Api\Rest;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use CampusCRM\EventTopicsBundle\Entity\EventTopics;

/**
 * @RouteResource("EventTopics")
 * @NamePrefix("oro_api_")
 */
class EventTopicsController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all Event Name items",
     *      resource=true
     * )
     * @AclAncestor("oro_eventtopics_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get eventtopics item",
     *      resource=true
     * )
     * @AclAncestor("oro_eventtopics_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id eventtopics item id
     *
     * @ApiDoc(
     *      description="Update eventtopics",
     *      resource=true
     * )
     * @AclAncestor("oro_eventtopics_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new eventtopics
     *
     * @ApiDoc(
     *      description="Create new eventtopics",
     *      resource=true
     * )
     * @AclAncestor("oro_eventtopics_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete eventtopics",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_eventtopics_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="EventTopicsBundle:EventTopics"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('event_topics.eventtopics.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('event_topics.form.eventtopics.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('event_topics.form.handler.eventtopics.api');
    }

    protected function getPreparedItem($entity, $resultFields = [])
    {
        $result = parent::getPreparedItem($entity, $resultFields);

        /** @var AmountProvider $amountProvider */
        $amountProvider = $this->get('oro_channel.provider.lifetime.amount_provider');

        $result['lifetimeValue'] = $amountProvider->getAccountLifeTimeValue($entity);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPreparedItems($entities, $resultFields = [])
    {
        $result = [];
        $ids = array_map(
            function(eventtopics $eventtopics) {
                return $eventtopics->getId();
            },
            $entities
        );

        $ap = $this->get('oro_channel.provider.lifetime.amount_provider');
        $lifetimeValues = $ap->getAccountsLifetimeQueryBuilder($ids)
            ->getQuery()
            ->getArrayResult();
        $lifetimeMap = [];
        foreach ($lifetimeValues as $value) {
            $lifetimeMap[$value['accountId']] = (float)$value['lifetimeValue'];
        }

        foreach ($entities as $entity) {
            /** @var eventtopics $entity */
            $entityArray = parent::getPreparedItem($entity, $resultFields);
            if (array_key_exists($entity->getId(), $lifetimeMap)) {
                $entityArray['lifetimeValue'] = $lifetimeMap[$entity->getId()];
            } else {
                $entityArray['lifetimeValue'] = 0.0;
            }

            $result[] = $entityArray;
        }

        return $result;
    }
}
