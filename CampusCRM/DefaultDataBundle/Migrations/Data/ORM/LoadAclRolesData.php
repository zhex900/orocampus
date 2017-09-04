<?php

namespace CampusCRM\DefaultDataBundle\Migrations\Data\ORM;

use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

abstract class LoadAclRolesData extends AbstractLoadAclData
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'CampusCRM\DefaultDataBundle\Migrations\Data\ORM\LoadRolesData',
        ];
    }
}
