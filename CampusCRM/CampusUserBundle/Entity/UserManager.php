<?php

namespace CampusCRM\CampusUserBundle\Entity;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Entity\UserManager as BaseManager;

class UserManager extends BaseManager
{
    /**
     * {@inheritdoc}
     */
    public function updateUser(UserInterface $user, $flush = true)
    {
        // make sure user has a default business unit
        if ($user instanceof User && $user->getBusinessUnits()->isEmpty()){
            /* @var BusinessUnit */
            $default_business_unit =  $this
                ->getStorageManager()
                ->getRepository('OroOrganizationBundle:BusinessUnit')
                ->find('1');
            if ($default_business_unit != null) {
                $user->addBusinessUnit($default_business_unit);
            }
        }
        return parent::updateUser($user, $flush);
    }

    /**
     * Check if user has particular role
     *
     * @param User $user
     * @param string $roleLabel Role Label
     *
     * @return bool
     */
    public function hasRole(User $user, $roleLabel)
    {
        /** @var Role $item */
        foreach ($user->getRoles() as $item) {
            if ($roleLabel === $item->getLabel()) {
                return true;
            }
        }
        return false;
    }
}
