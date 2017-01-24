<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/1/17
 * Time: 10:11 PM
 */

// src/CampusCRM/CampusContactBundle/CampusContactBundle.php
namespace CampusCRM\CampusContactBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CampusContactBundle extends Bundle
{
    public function getParent()
    {
        return 'OroContactBundle';
    }
}