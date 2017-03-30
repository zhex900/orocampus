<?php

namespace CampusCRM\EventNameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use CampusCRM\EventNameBundle\Entity\EventName;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class EventNameController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_eventname_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_eventname_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="EventNameBundle:EventName"
     * )
     * @Template()
     */
    public function viewAction(EventName $eventname)
    {
        return [
            'entity' => $eventname
        ];
    }

    /**
     * Create eventname form
     *
     * @Route("/create", name="oro_eventname_create")
     * @Acl(
     *      id="oro_eventname_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="EventNameBundle:EventName"
     * )
     * @Template("EventNameBundle:EventName:update.html.twig")
     */
    public function createAction()
    {
        return $this->update();
    }

    /**
     * @param EventName $entity
     * @return array
     */
    protected function update(EventName $entity = null)
    {
        if (!$entity) {
            $entity = $this->getManager()->createEntity();
        }

        return $this->get('oro_form.model.update_handler')->update(
            $entity,
            $this->get('event_name.form.eventname'),
            $this->get('translator')->trans('oro.eventname.controller.eventname.saved.message'),
            $this->get('event_name.form.handler.eventname')
        );
    }

    /**
     * @return ApiEntityManager
     */
    protected function getManager()
    {
        return $this->get('event_name.eventname.manager.api');
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_eventname_update", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_eventname_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="EventNameBundle:EventName"
     * )
     * @Template()
     */
    public function updateAction(EventName $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_eventname_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @AclAncestor("oro_eventname_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('event_name.entity.eventname.class')
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="oro_eventname_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("oro_eventname_view")
     * @Template()
     */
    public function infoAction(EventName $eventname)
    {
        // var_dump($eventname->getName()); die();
        return [
            'eventname' => $eventname
        ];
    }
}
