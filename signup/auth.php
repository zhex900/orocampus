<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 10/02/2015
 * Time: 3:40 PM
 */

// session started in zurmo.php
include("zurmo.php");

// fetch data from login form.
$user = $_REQUEST['usernames'];
$pass = $_REQUEST['pass'];
$formType = $_REQUEST['form_types'];
$contactSource = $_REQUEST['source_of_contact'];

//try to authenticate with zurmo
$authorized = login($user, $pass);

// checking if the user logged in successfully
if ($authorized) {
    // authentication success, store the login details
    $_SESSION['username'] = $user;
    $_SESSION['form'] = "forms/".$formType;
    $_SESSION['contactSource'] = $contactSource;

    // redirect to the selected form.
    header("Location: forms/" . $formType);
} else {
    // authentication failed
    // return to login page, indicate incorrect username and password.
    $_SESSION['error'] = 'Incorrect username and/ or password';
    header("Location: login.php");
}