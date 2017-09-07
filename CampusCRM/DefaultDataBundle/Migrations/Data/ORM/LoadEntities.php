<?php

namespace CampusCRM\DefaultDataBundle\Migrations\Data\ORM;

use CampusCRM\EventNameBundle\Entity\EventName;
use CampusCRM\EventTopicsBundle\Entity\EventTopics;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Entity\SystemCalendar;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEntities extends AbstractFixture implements ContainerAwareInterface
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
        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@DefaultDataBundle/Migrations/Data/ORM/dictionaries');

        $this->organization = $this->em->getRepository('OroOrganizationBundle:Organization')->find(1);
        $this->admin = $this->em->getRepository('OroUserBundle:User')->find(1);

        $this->LoadEntities($dictionaryDir,'system_calendars.csv',[$this,'createSystemCalendar']);
        $this->LoadEntities($dictionaryDir,'event_names.csv',[$this,'createEventName']);
        $this->LoadEntities($dictionaryDir,'event_topics.csv',[$this,'createEventTopics']);
        $this->LoadEntities($dictionaryDir,'calendar_events.csv',[$this,'createCalendarEvents']);
    }

    /**
     * @param  array $data
     * @return CalendarEvent
     */
    protected function createCalendarEvents(array $data)
    {
        /* @var CalendarEvent*/
        $event = null;
        $calendar = $this
            ->em
            ->getRepository('OroCalendarBundle:SystemCalendar')
            ->findBy(['name'=>$data['Calendar']]);
        $event_name = $this
            ->em
            ->getRepository('EventNameBundle:EventName')
            ->findBy(['name'=>$data['Name']]);
        if (sizeof($calendar) != 0 && sizeof($event_name) !=0 ) {
            $event = new CalendarEvent();
            $event->setAllDay(true);
            $event->setOroEventname($event_name[0]);
            $event->setTitle($data['Name']);
            $event->setStart(
                \DateTime::createFromFormat(
                    'd/m/Y H:i:s',
                    $data['Start'].' '.'00:00:00'));
            $event->setEnd(
                \DateTime::createFromFormat(
                    'd/m/Y H:i:s',
                    $data['End'].' '.'00:00:00'));
            $event->setSystemCalendar($calendar[0]);
        }
        return $event;
    }

    /**
     * @param  array $data
     *
     * @return SystemCalendar
     */
    protected function createSystemCalendar(array $data)
    {
        $calendar = $this
            ->em
            ->getRepository('OroCalendarBundle:SystemCalendar')
            ->findBy(['name'=>$data['Calendar']]);
        $system_calendar = null;
        if (sizeof($calendar)==0) {
            /* @var SystemCalendar */
            $system_calendar = new SystemCalendar();
            $system_calendar->setPublic(true);
            $system_calendar->setName($data['Calendar']);
            $system_calendar->setBackgroundColor($data['Colour']);
            $system_calendar->setOrganization($this->organization);
        }
        return $system_calendar;
    }
    /**
     * @param  array $data
     *
     * @return EventName
     */
    protected function createEventName(array $data)
    {
        $eventname = new EventName();
        $eventname->setName($data['Name']);
        $eventname->setOwner($this->admin);
        $eventname->setOrganization($this->organization);
        $eventname->setSystemCalendar($data['System']);
        return $eventname;
    }
    /**
     * @param  array $data
     *
     * @return EventTopics
     */
    protected function createEventTopics(array $data)
    {
        $eventtopic = new EventTopics();
        $eventtopic->setName($data['Name']);
        $eventtopic->setOwner($this->admin);
        $eventtopic->setOrganization($this->organization);
        return $eventtopic;
    }

    /**
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        if (isset($object)) {
            $manager->persist($object);
        }
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

    /**
     * @param string $dictionaryDir
     * @param string $data_file
     * @param callable $callback
     * @return object
     */
    protected function LoadEntities($dictionaryDir, $data_file, $callback)
    {
        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR. $data_file, "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                $object = $callback($data);
                $this->persist($this->em, $object);
                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->flush($this->em);
                }
            }
            $this->flush($this->em);
            fclose($handle);
        }
    }
}
