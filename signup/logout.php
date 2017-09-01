<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2/19/2015
 * Time: 5:12 AM
 */

session_start();
if(session_destroy()) // Destroying All Sessions
{
    header("Location: login.php"); // Redirecting To Home Page
}