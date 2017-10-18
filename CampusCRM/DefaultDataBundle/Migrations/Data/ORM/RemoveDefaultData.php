<?php

namespace CampusCRM\DefaultDataBundle\Migrations\Data\ORM;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class RemoveDefaultData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var  EntityManager */
    protected $em;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->removaAll('OroReportBundle:Report');
        $this->removaAll('OroUserBundle:Group');
        $this->removaAll('OroContactBundle:Group');
        $this->removaAll('OroWorkflowBundle:ProcessDefinition');

        $entities = $this->em->getRepository('OroWorkflowBundle:WorkflowDefinition')->findAll();
        foreach ($entities as $entity) {
            $entity->setSystem(false);
            $this->em->remove($entity);
        }

        $entities = $this->em->getRepository('OroUserBundle:Role')->findAll();
        foreach ($entities as $entity) {
            if ($entity->getLabel()!='Administrator') {
                $this->em->remove($entity);
            }
        }

        $entities = $this->em->getRepository('OroDashboardBundle:Widget')->findAll();
        foreach ($entities as $entity) {
            if (!in_array($entity->getName(),
                [
                    'quick_launchpad',
                    'my_calendar',
                    'my_contacts_activity',
                    'recent_calls',
                    'recent_emails'
                ])) {
                $this->em->remove($entity);
            }
        }
        $this->em->flush();
    }

    /**
     * Remove entities
     *
     * @param string $entity_id
     */
    private function removaAll($entity_id)
    {
        $entities = $this->em->getRepository($entity_id)->findAll();
        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }
    }
}
