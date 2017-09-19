<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 18/9/17
 * Time: 8:42 PM
 */

namespace CampusCRM\CampusContactBundle\Command;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;

class TransitionAssignCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME   = 'oro:cron:contact:transition_assign';

    /**
     * Run every minute
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '* * * * *';
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

    /*
     * Transitions: Unassigned to Assigned
     * Condition:   Owner role is Full-timer
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Auto transition: from unassigned to assigned');

        // Get a list of all the contacts that are at unassigned step
        /** @var Contact[] $contacts */
        $contacts = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroContactBundle:Contact')
            ->findByWorkflowStep('contact_followup','unassigned');

        foreach ($contacts as $contact){
            $output->writeln( 'Check '.$contact->getFirstName(). ' '. $contact->getLastName());
            // Transit if the owner is a Full-timer
            if ($contact->getOwner() instanceof User && $contact->getOwner()->getRole('FULL_TIMER')!=null){
                $output->writeln('Transit '.$contact->getFirstName(). ' '. $contact->getLastName(). ' to assigned');
                $this
                    ->getContainer()
                    ->get('campus_contact.workflow.manager')
                    ->transitFromTo($contact,'contact_followup','unassigned','assign');
            }
        }

        // var_dump((print_r($result,true)));
       // $calendarDateManager->handleCalendarDates(true);



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