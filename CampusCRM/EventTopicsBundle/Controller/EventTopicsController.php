<?php

namespace CampusCRM\EventTopicsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use CampusCRM\EventTopicsBundle\Entity\EventTopics;

class EventTopicsController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_eventtopics_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_eventtopics_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="EventTopicsBundle:EventTopics"
     * )
     * @Template()
     */
    public function viewAction(EventTopics $eventtopics)
    {
        return [
            'entity' => $eventtopics
        ];
    }

    /**
     * Create eventtopics form
     *
     * @Route("/create", name="oro_eventtopics_create")
     * @Acl(
     *      id="oro_eventtopics_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="EventTopicsBundle:EventTopics"
     * )
     * @Template("EventTopicsBundle:EventTopics:update.html.twig")
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * @param EventTopics $entity
     * @return array
     */
    protected function update(EventTopics $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        return $this->get('oro_form.model.update_handler')->update(
            $entity,
            $this->get('event_topics.form.eventtopics'),
            $this->get('translator')->trans('oro.eventtopics.controller.eventtopics.saved.message'),
            $this->get('event_topics.form.handler.eventtopics')
        );
    }

    /**
     * @return ApiEntityManager
     */
    protected function getManager()
    {
        return $this->get('event_topics.eventtopics.manager.api');
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_eventtopics_update", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_eventtopics_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="EventTopicsBundle:EventTopics"
     * )
     * @Template()
     */
    public function updateAction(EventTopics $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_eventtopics_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @AclAncestor("oro_eventtopics_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('event_topics.entity.eventtopics.class')
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="oro_eventtopics_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("oro_eventtopics_view")
     * @Template()
     */
    public function infoAction(EventTopics $eventtopics)
    {
        return [
            'eventtopics' => $eventtopics
        ];
    }
}
