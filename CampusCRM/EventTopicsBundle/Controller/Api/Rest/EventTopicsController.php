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
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

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

    public function getFormHandler()
    {
        return $this->get('event_name.form.handler.eventname.api');
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
}
