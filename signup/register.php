<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 12/02/2015
 * Time: 9:11 PM
 */

include("orocampus.php");

// response messages
$NEWCONTACT_RESPONSE_TITLE = "Registration Successful!";
$EXISTINGCONACT_REPSONSE_TITLE = "Already Registered";
$NEWCONTACT_RESPONSE = "Thank you for signing up " . ucwords(strtolower($_REQUEST['fname'])) . "!";

/** @var orocampus $api */
$api = new orocampus(URL, LOGIN, APIKEY,$_SESSION,$_REQUEST);

// form actions
switch ($_SESSION['form']) {
    case 'forms/club_registration.php':
        // create contact only when the email is unique
        if ($api->findContactByEmail()==null) {
            // create new contact
            $contact_id = $api->createContact($api->getContact());
            $api->getLogger()->info('Create new contact '. ucwords(strtolower($_REQUEST['fname'])));
            if (isset($contact_id)){
                $api->getLogger()->info('New contact '. $contact_id);
                $api->addAddress($contact_id);
                $_SESSION['response_msg_title'] = $NEWCONTACT_RESPONSE_TITLE;
                $_SESSION['response_msg'] = $NEWCONTACT_RESPONSE;
                // add contact to selected event
            }else{
                //create contact failed
                $_SESSION['response_msg_title'] = "Registration failed";
                $_SESSION['response_msg'] = "Please try again";
            }
        }else{
            $_SESSION['response_msg_title'] = "Registration failed";
            $_SESSION['response_msg'] = "Your email is used by another person. Please register with another email.";
        }
    case 'form/':
        //do something
}
header("Location: registration_response.php");

