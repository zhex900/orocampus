<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 18/9/17
 * Time: 8:42 PM
 */

namespace CampusCRM\CampusContactBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;

class TransitionAndReviewCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME = 'oro:cron:contact:transition_review';

    /**
     * Daily at 4 am.
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '0 15 * * *';
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
            ->setDescription('Auto transition');
    }

    /*
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Apply transition rules.');
        $this->getContainer()
            ->get('campus_contact.workflow.manager')
            ->runTransitRulesForContactFollowup();

        $output->writeln('Apply review rules.');
        $this->getContainer()
            ->get('campus_contact.review.manager')
            ->applyReviewRulesForContactFollowUp();

        return self::STATUS_SUCCESS;
    }
}