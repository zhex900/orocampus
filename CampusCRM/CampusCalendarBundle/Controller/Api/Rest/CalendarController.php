<?php

namespace CampusCRM\CampusCalendarBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\CalendarBundle\Controller\Api\Rest\CalendarController as BaseController;

/**
 * @RouteResource("calendar")
 * @NamePrefix("oro_api_")
 */
class CalendarController extends BaseController
{
    /**
     * Get All the Calendars of the Enabled Users
     *
     * @Get("/calendars/all")
     *
     * @ApiDoc(
     *      description="Get all the calendars of the enabled users",
     *      resource=true
     * )
     * @AclAncestor("oro_calendar_view")
     *
     * @return Response
     */
    public function getAllAction()
    {
        /** @var array $calendars */
        $calendars = $this->getDoctrine()->getManager()->getRepository('OroCalendarBundle:Calendar')->findAll();
        $result=null;
        foreach ($calendars as $calendar){
            /** @var Calendar $calendar **/
            if ($calendar->getOwner()->isEnabled()) {
                $result[] = [
                    'id' => $calendar->getId(),
                    'owner_name' => $calendar->getOwner()->getFirstName() . ' ' . $calendar->getOwner()->getLastName(),
                    'owner_id' => $calendar->getId()];
            }
        }
        return new Response(json_encode($result), Codes::HTTP_OK);
    }
}