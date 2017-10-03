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

class TransitionDailyCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME = 'oro:cron:contact:transition_daily';

    /**
     * Daily at midnight
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '0 0 * * *';
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

        $this->getContainer()
            ->get('campus_contact.review.manager')
            ->applyReviewRulesForContactFollowUp();

        return self::STATUS_SUCCESS;
    }
}