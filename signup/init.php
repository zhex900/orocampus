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

$formType = $_REQUEST['form_types'];
$_SESSION['form'] = "forms/" . $formType;
$_SESSION['contactSource'] = $_REQUEST['source_of_contact'];
$_SESSION['countries'] = json_decode(file_get_contents("./data/countries.json"), true);

file_put_contents('/tmp/signup.log','source: '. $_SESSION['contactSource'].PHP_EOL,FILE_APPEND);

// redirect to the selected form.
header("Location: forms/" . $formType);