<?php

namespace CampusCRM\CampusActivityBundle\Entity\Manager;

use Oro\Bundle\ActivityBundle\Entity\Manager\ActivityContextApiEntityManager as BaseManager;

class ActivityContextApiEntityManager extends BaseManager
{
    /**
     * Returns the context for the given activity class and id
     *
     * @param string $class The FQCN of the activity entity
     * @param        $id
     *
     * @return array
     *
     * Filter out User and Contact targets when called from CalendarEvent
     */
    public function getActivityContext($class, $id)
    {
        $targets = parent::getActivityContext($class,$id);
        $result= [];

        foreach ($targets as $target) {

            if ($class == 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent' &&
                $target['targetClassName'] == 'Oro_Bundle_ContactBundle_Entity_Contact' ||
                $target['targetClassName'] == 'Oro_Bundle_UserBundle_Entity_User'){
                continue;
            }
            $result[]=$target;
        }

        return $result;
    }
}
