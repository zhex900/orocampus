<?php

namespace CampusCRM\CampusContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadEthnicitySourceData extends AbstractFixture
{
    /** @var array */
    protected $data = [
        'Caucasian' => false,
        'Chinese Asian' => false,
        'Southern Asian' => false,
        'African' => false,
        'Arab' => false,
        'Hispanic' => false,
        'Jewish' => false,
        'Australian Aboriginal' => false,
        'Torres Strait Islander' => false,
        'Other' => false

    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('ethnicity_source');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, $isDefault);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
