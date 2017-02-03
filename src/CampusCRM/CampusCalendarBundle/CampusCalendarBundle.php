<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/1/17
 * Time: 10:11 PM
 */

// src/CampusCRM/CampusCalendarBundle/CampusCalendarBundle.php
namespace CampusCRM\CampusCalendarBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CampusCalendarBundle extends Bundle
{
    public function getParent()
    {
        return 'OroCalendarBundle';
    }
}