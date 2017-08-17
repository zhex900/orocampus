<?php

namespace CampusCRM\CampusCalendarBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use CampusCRM\CampusCalendarBundle\Manager\AttendeeManager;
use Oro\Bundle\CalendarBundle\Form\DataTransformer\AttendeesToViewTransformer as BaseTransformer;

class AttendeesToViewTransformer extends BaseTransformer
{
    /** @var AttendeeManager */
    protected $attendeeManager;

    /**
     * @param EntityManager $entityManager
     * @param TokenStorageInterface $securityTokenStorage
     * @param AttendeeManager $attendeeManager
     */
    public function __construct(
        EntityManager $entityManager,
        TokenStorageInterface $securityTokenStorage,
        AttendeeManager $attendeeManager
    ) {
        parent::__construct($entityManager, $securityTokenStorage, $attendeeManager);

        $this->attendeeManager = $attendeeManager;
    }

}
