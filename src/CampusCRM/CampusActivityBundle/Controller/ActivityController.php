<?php

namespace CampusCRM\CampusActivityBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ActivityBundle\Controller\ActivityController as BaseController;

class ActivityController extends BaseController
{
    protected function getSupportedTargets($entity)
    {
        $entityClass = ClassUtils::getClass($entity);
        $targetClasses = array_keys($this->getActivityManager()->getActivityTargets($entityClass));
        $targetClasses = $this->get('campus_activity.manger.filter')->filter($entityClass,$targetClasses);

        return $this->getMultiGridProvider()->getEntitiesData($targetClasses);
    }
}
