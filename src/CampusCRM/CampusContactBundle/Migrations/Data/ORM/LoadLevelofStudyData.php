<?php

namespace CampusCRM\CampusContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadLevelofStudyData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('level_of_study_source');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        $dir = getcwd() . "/src/CampusCRM/CampusContactBundle/Migrations/Data/ORM/dictionaries/";
        $handle = fopen($dir . "level_of_study.csv", "r");

        while (!feof($handle)) {
            $data = fgets($handle);
            if (!empty($data)) {
                $enumOption = $enumRepo->createEnumValue($data, $priority++, false);
                $manager->persist($enumOption);
            }
        }

        fclose($handle);
        $manager->flush();
    }
}
