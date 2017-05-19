<?php

namespace CampusCRM\CampusActivityBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\DataGridBundle\Provider\MultiGridProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Bundle\ActivityBundle\Controller\ActivityController as BaseController;


class ActivityController extends BaseController
{

    /**
     * @param object $entity
     *
     * @return array
     * [
     *     [
     *         'label' => label,
     *         'gridName' => gridName,
     *         'className' => className,
     *     ],
     * ]
     */
    protected function getSupportedTargets($entity)
    {
        $entityClass = ClassUtils::getClass($entity);
        $targetClasses = array_keys($this->getActivityManager()->getActivityTargets($entityClass));

        $userClass = 'Oro\Bundle\UserBundle\Entity\User';
        $contactClass = 'Oro\Bundle\ContactBundle\Entity\Contact';

        if ($entityClass == 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent'){
            $targetClasses = array_diff( $targetClasses, [$userClass,$contactClass] );
        }

        return $this->getMultiGridProvider()->getEntitiesData($targetClasses);
    }
}
