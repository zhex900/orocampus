<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 26/7/17
 * Time: 10:04 PM
 */

namespace CampusCRM\CampusCalendarBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{

    /**
     * Overwrite project specific services
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $defNewService = $container->getDefinition('oro_calendar.calendar_event.update_child_manager');
        $defNewService ->setClass('CampusCRM\CampusCalendarBundle\Manager\CalendarEvent\UpdateChildManager');
    }
}