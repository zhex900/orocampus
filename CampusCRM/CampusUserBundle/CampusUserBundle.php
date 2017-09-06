<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/1/17
 * Time: 10:11 PM
 */

namespace CampusCRM\CampusUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use CampusCRM\CampusUserBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class CampusUserBundle extends Bundle
{
    public function getParent()
    {
        return 'OroUserBundle';
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