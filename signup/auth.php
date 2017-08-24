<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 10/02/2015
 * Time: 3:40 PM
 */

// session started in zurmo.php
include("orocampus.php");
// starting a session to enable session variables to be stored
session_start();
// fetch data from login form.
$formType = $_REQUEST['form_types'];
$contactSource = $_REQUEST['source_of_contact'];

$authorized = isAPIUp();
// checking if the user logged in successfully
if ($authorized) {
    // authentication success, store the login details
   // $_SESSION['username'] = $user;
    $_SESSION['form'] = "forms/".$formType;
    $_SESSION['contactSource'] = $contactSource;
    $_SESSION['sessionID'] = 'dfdfdfdfdfdfdf';

    file_put_contents('/tmp/signup.log','login'.PHP_EOL,FILE_APPEND);
    file_put_contents('/tmp/signup.log','source '.$_SESSION['contactSource'].PHP_EOL,FILE_APPEND);
    file_put_contents('/tmp/signup.log','sessionID '.$_SESSION['sessionID'].PHP_EOL,FILE_APPEND);

    // redirect to the selected form.
    header("Location: forms/" . $formType);
} else {
    // authentication failed
    // return to login page, indicate incorrect username and password.
    $_SESSION['error'] = 'Incorrect username and/ or password';
    header("Location: login.php");
}