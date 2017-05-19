<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 20/5/17
 * Time: 12:00 AM
 */

namespace CampusCRM\CampusActivityBundle\Manager;


class Context
{
    public function filter($class, $targetEntityClasses)
    {
        $userClass = 'Oro\Bundle\UserBundle\Entity\User';
        $contactClass = 'Oro\Bundle\ContactBundle\Entity\Contact';

        if ($class == 'Oro\Bundle\CalendarBundle\Entity\CalendarEvent') {
            $targetEntityClasses = array_diff($targetEntityClasses, [$userClass, $contactClass]);
        }
        return $targetEntityClasses;
    }
}