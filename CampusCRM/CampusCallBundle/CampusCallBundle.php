<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 24/1/17
 * Time: 10:11 PM
 */

namespace CampusCRM\CampusCallBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CampusCallBundle extends Bundle
{
    public function getParent()
    {
        return 'OroCallBundle';
    }
}