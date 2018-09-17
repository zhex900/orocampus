<?php
/**
 * Created by PhpStorm.
 * User: jake he
 * Date: 24/1/17
 * Time: 10:11 PM
 */

// src/CampusCRM/AddressBundle/CampusAddressBundle.php
namespace CampusCRM\AddressBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CampusAddressBundle extends Bundle
{
    public function getParent()
    {
        return 'OroAddressBundle';
    }
}
