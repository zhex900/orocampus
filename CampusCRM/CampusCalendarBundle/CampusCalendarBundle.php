<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/1/17
 * Time: 10:11 PM
 */

namespace CampusCRM\CampusCalendarBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use CampusCRM\CampusCalendarBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class CampusCalendarBundle extends Bundle
{
    public function getParent()
    {
        return 'OroCalendarBundle';
    }
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}