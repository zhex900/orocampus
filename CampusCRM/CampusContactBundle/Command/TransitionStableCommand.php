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

class TransitionStableCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME   = 'oro:cron:contact:transition_stable';

    /**
     * Run every day
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '50 * * * *';
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
            ->setDescription('Fire stable transition');
    }

    /*
     * Transitions: Followup to Stable
     * Condition:   Satisfy stable criteria. For example:
     *              Meet 3 times over 5 weeks. Each meeting is
     *              5 days apart.
     *
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Auto transition: from followup to stable');

        // Get a list of all the contacts that are at followup step
        /** @var Contact[] $contacts */
        $contacts = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroContactBundle:Contact')
            ->findByWorkflowStep('contact_followup','followup');


        $today = $this->getContainer()->get('frequency_manager')->today();
        $teaching_week = $this->getContainer()->get('academic_calendar')->getTeachingWeek($today);
        $semester = $this->getContainer()->get('academic_calendar')->getCurrentSemester();

        $output->writeln('Today: '.$today->format('Y-m-d').', week: '.$teaching_week.', semester: '. $semester);

        foreach ($contacts as $contact){
            $output->writeln( 'Check '.$contact->getFirstName(). ' '. $contact->getLastName());

            $events = $this
                ->getContainer()
                ->get('frequency_manager')->findAttendedEvents($contact,null,$semester);

            var_dump(print_r($events,true));

            $freq = $this
                ->getContainer()
                ->get('frequency_manager')->findAttendanceFrequency($today,$teaching_week,$events,0);

            $output->writeln( 'Freq: '.$freq);

            // Transit if the contact is having regular meeting
            if ($freq === $this
                    ->getContainer()
                    ->get('frequency_manager')->getRegular()){
                $output->writeln('Transit '.$contact->getFirstName(). ' '. $contact->getLastName(). ' to stable');
              /*  $this
                    ->getContainer()
                    ->get('campus_contact.workflow.manager')
                    ->transitFromTo($contact,'contact_followup','followup','stable');*/
            }
        }
        return self::STATUS_SUCCESS;
    }
}