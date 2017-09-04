<?php

namespace CampusCRM\CampusContactBundle\Migrations\Data\ORM;

use CampusCRM\EventNameBundle\Entity\EventName;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventName extends AbstractFixture implements ContainerAwareInterface
{
    const FLUSH_MAX = 50;

    /** @var ContainerInterface */
    protected $container;

    /** @var User */
    protected $admin;

    /** @var  EntityManager */
    protected $em;

    /** @var  ConfigManager */
    protected $configManager;

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->configManager = $container->get('oro_entity_config.config_manager');
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if ($manager) {
            $this->em = $manager;
        }
        $this->admin = $this->em->getRepository('OroUserBundle:User')->find(1);
        $this->organization = $this->em->getRepository('OroOrganizationBundle:Organization')->find(1);

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@DefaultDataBundle/Migrations/Data/ORM/dictionaries');

        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR. "event_names.csv", "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }

            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                file_put_contents('/tmp/load.log', '$headers:'.print_r($headers,true).PHP_EOL, FILE_APPEND);
                file_put_contents('/tmp/load.log', 'data:'.print_r($data,true).PHP_EOL, FILE_APPEND);
                $data = array_combine($headers, array_values($data));
                $eventname = $this->createEventName($manager,$data);

                $this->persist($this->em, $eventname);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->flush($this->em);
                }
            }

            $this->flush($this->em);
            fclose($handle);
        }
    }
    /**
     * @param  array $data
     *
     * @return EventName
     */
    protected function createEventName(ObjectManager $manager, array $data)
    {
        $eventname = new EventName();
        $eventname->setName($data['Name']);
        $eventname->setOwner($this->admin);
        $eventname->setOrganization($this->organization);
        $eventname->setSystemCalendar($data['System']);
        return $eventname;
    }

    /**
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        $manager->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }
}
