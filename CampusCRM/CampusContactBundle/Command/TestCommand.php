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

class TestCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME = 'oro:cron:contact:test';

    /**
     * Run every 15 minute
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/15 * * * *';
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

        // TODO: change show_step_in_grid to false in contact configure.

        $output->writeln('Test ');

//        $configManager = $this->getContainer()
//            ->get('oro_entity_config.config_manager');
//
//        $configProvider = $this->getContainer()
//            ->get('oro_entity_config.config_manager');

        // $configProvider->getConfig('Oro\Bundle\ContactBundle\Entity\Contact')->set('show_step_in_grid',0);
//
//        $this->getContainer()
//            ->get('oro_entity_config.provider.workflow')
//            ->getConfig('Oro\Bundle\ContactBundle\Entity\Contact')->set('show_step_in_grid', 0);

        $report = $this->getContainer()
            ->get('doctrine.orm.entity_manager')->getRepository('OroReportBundle:Report')->findOneBy(['name'=>'Campaign Performance']);

        if ($report !=null ){
            $output->writeln('Report find: '. $report->getEntity());
            $this->getContainer()
                ->get('doctrine.orm.entity_manager')->remove($report);
            $this->getContainer()
                ->get('doctrine.orm.entity_manager')->flush();
        }
        else{
            $output->writeln('Report not find ');
        }
        return self::STATUS_SUCCESS;
    }
}