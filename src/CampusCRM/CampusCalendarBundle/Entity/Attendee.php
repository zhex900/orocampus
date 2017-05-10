<?php

namespace CampusCRM\CampusCalendarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\CalendarBundle\Entity\Attendee as BaseAttendee;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;

class Attendee extends BaseAttendee
{
    /**
     * @return User
     */
    public function getUser()
    {
        parent::getUser();

        /* @var User $user */
        $user = new User();
        /* @var Organization $org */
        $org = new Organization();
        ReflectionUtil::setId($org, 1);
        $user->setOrganization($org);
        $user->setId(10000);
        $user->setFirstName('Test_FirstName');
        $user->setUsername('test1');
      //  $user->setA
        file_put_contents('/tmp/search.log',PHP_EOL .'getUser',FILE_APPEND);
        //var_dump('get'); die;
        return $user; //$this->user;
    }

    /**
     * @param User $user
     *
     * @return Attendee
     */
    public function setUser(User $user = null)
    {
        parent::setUser($user);

        file_put_contents('/tmp/search.log',PHP_EOL .'setUser'. $user->getFirstName(),FILE_APPEND);
        return $this;
    }

}