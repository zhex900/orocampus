<?php

namespace CampusCRM\EventNameBundle\Controller\Api\Rest;

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
use CampusCRM\EventNameBundle\Entity\EventName;

/**
 * @RouteResource("EventName")
 * @NamePrefix("oro_api_")
 */
class EventNameController extends RestController implements ClassResourceInterface
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
     * @AclAncestor("oro_eventname_view")
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
     *      description="Get eventname item",
     *      resource=true
     * )
     * @AclAncestor("oro_eventname_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id eventname item id
     *
     * @ApiDoc(
     *      description="Update eventname",
     *      resource=true
     * )
     * @AclAncestor("oro_eventname_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new eventname
     *
     * @ApiDoc(
     *      description="Create new eventname",
     *      resource=true
     * )
     * @AclAncestor("oro_eventname_create")
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
     *      description="Delete eventname",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_eventname_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="EventNameBundle:EventName"
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
        return $this->get('event_name.eventname.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('event_name.form.eventname.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('event_name.form.handler.eventname.api');
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
            function(eventname $eventname) {
                return $eventname->getId();
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
            /** @var eventname $entity */
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
