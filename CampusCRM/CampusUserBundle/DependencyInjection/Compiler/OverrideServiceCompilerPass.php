<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 26/7/17
 * Time: 10:04 PM
 */

namespace CampusCRM\CampusUserBundle\DependencyInjection\Compiler;

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
        $container
            ->getDefinition('oro_user.manager')
            ->setClass('CampusCRM\CampusUserBundle\Entity\UserManager');
    }
}