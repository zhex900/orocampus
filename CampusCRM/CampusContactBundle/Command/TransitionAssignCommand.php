<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 18/9/17
 * Time: 8:42 PM
 */

namespace CampusCRM\CampusContactBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\OroContactBundle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;

class TransitionAssignCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME   = 'oro:cron:contact:transition_assign';

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '01 00 * * *';
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Fire assign transition');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Fire assign transition!'.get_class(new contact()));

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Contact[] $result */
        $result = $em->createQueryBuilder()
            ->select('c')
            ->from('OroWorkflowBundle:WorkflowItem', 'wi')
            ->innerJoin('OroContactBundle:Contact','c', 'WITH','c.id = wi.entityId')
            ->innerJoin('wi.currentStep','step', Join::WITH, 'step.name = :step')
            ->innerJoin('wi.definition', 'workflowDefinition', Join::WITH, 'workflowDefinition.name = :workflow')
            ->setParameter('workflow', 'contact_followup')
            ->setParameter('step','unassigned')
            ->getQuery()
            ->execute();

        foreach ($result as $contact){
            $output->writeln( $contact->getFirstName(). ' '. $contact->getLastName());
        }

        $result = $em->getRepository('OroContactBundle:Contact')->findByWorkflowStep('contact_followup','unassigned');
        foreach ($result as $contact){
            $output->writeln( $contact->getFirstName(). ' '. $contact->getLastName());
        }

        // var_dump((print_r($result,true)));
       // $calendarDateManager->handleCalendarDates(true);

        /*
         * Transitions: Unassigned to Assigned
         * Condition:   Owner role is Full-timer
         */

        /*
         * Transitions: Followup to Stable
         * Condition:   Satisfy stable criteria. For example:
         *              Meet 3 times over 5 weeks. Each meeting is
         *              5 days apart.
         */

        /*
         * Transitions: Stable to Followup
         * Condition:   Fails stable criteria.
         */

        return self::STATUS_SUCCESS;
    }
}