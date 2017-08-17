<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/1/17
 * Time: 10:11 PM
 */

namespace CampusCRM\CampusUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CampusUserBundle extends Bundle
{
    public function getParent()
    {
        return 'OroUserBundle';
    }
}