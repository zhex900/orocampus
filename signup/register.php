<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 12/02/2015
 * Time: 9:11 PM
 */

include("orocampus.php");

// an array representing the activities the registering user is interested in
$ACTIVITIES = Array('Not interested', 'Not interested', 'Not interested', 'Not interested', 'Not interested');

// fetching data from the form
$userState = $_REQUEST['userstate'];
$form = $_REQUEST['form'];
$source = $_SESSION['contactSource'];
$type = $_REQUEST['type'];

// user information
$firstname = ucwords ( strtolower ($_REQUEST['fname'] ));
$lastname = ucwords ( strtolower ($_REQUEST['lname']));
$studentId = $_REQUEST['student_id'];
$email = strtolower($_REQUEST['email']);
$gender = $_REQUEST['gender'];
if ($_REQUEST['dob']=="") { $_REQUEST['dob']= "01/01/1900"; }
$dob = date_format(date_create_from_format('d/m/Y', $_REQUEST['dob']), 'Y-m-d');
$intstudent = $_REQUEST['int_student'];

// names of array indexes are named according to zurmo
$address = Array
(
    //'street1' => $_REQUEST['address'],
    'street1' => $_REQUEST['street_number'] . " ". $_REQUEST['street_name'],
    'city' => $_REQUEST['city'],
    'state' => $_REQUEST['state'],
    'postalCode' => $_REQUEST['postal_code'],
    'country' => $_REQUEST['country'],
);

// make the whole address in line one if the address is not find in Google map.
if ($_REQUEST['street_number']=='') {
    $address['street1'] = ucwords ( strtolower ($_REQUEST['address']));
}

$mobile = $_REQUEST['mobile'];
$telephone = $_REQUEST['telephone'];
$degree = $_REQUEST['degree'];
$course = $_REQUEST['course'];
$uni = $_REQUEST['uni'];
$year = $_REQUEST['year'];
$religion = $_REQUEST['areyouchristian'];
$country = $_REQUEST['countryorigin'];

// checking if any activity checkboxes were selected
if (!empty($_REQUEST['enquiries'])) {
    $enquiries = $_REQUEST['enquiries'];

    // for each checkbox the user ticked change that activity from Not interested to Interested
    for ($i = 0; $i <= count($ACTIVITIES); $i++) {
        foreach ($enquiries as $enquiry) {
            if ($i == $enquiry) {
                $ACTIVITIES[$i] = 'Interested';
            }
        }
    }
}

$NEWCONTACT_RESPONSE_TITLE = "Registration Successful!";
$EXISTINGCONACT_REPSONSE_TITLE = "Already Registered";
$NEWCONTACT_RESPONSE =  "Thank you for signing up " . $firstname . "!";
$EXISTINGCONACT_REPSONSE = "Looks like you have already registered. <br> Thanks for coming back " . $firstname . "!";

$leadDetails = Array
(

    'source' => Array
    (
        'value' => $source
    ),

    'typeCstm' => Array
    (
        'value' => $type
    ),

    'state' => Array
    (
        'id' => $userState
    ),

    'firstName' => $firstname,
    'lastName' => $lastname,
    'studidCstm' => $studentId,

    'primaryEmail' => Array
    (
        'emailAddress' => $email
    ),

    'dobCstm' => $dob,

    'genderCstm' => Array(
        'value' => $gender
    ),

    'intstudentCstm' => Array (
        'value' => $intstudent
    ),

    'countryCstm' => Array (
        'value' => $country
    ),

    'primaryAddress' => $address,
    'mobilePhone' => $mobile,
    'homephoneCstm' => $telephone,

    'degreeCstm' => Array (
        'value' => $degree
    ),

    'courseCstm' => Array (
        'value' => $course
    ),

    'industry' => Array (
        'value' => $uni
    ),

    'yearCstm' => Array (
        'value' => $year
    ),

    'backgroundCstm' => Array (
        'value' => $religion
    ),

    'activitiesCstm' => Array (
        'value' => $ACTIVITIES[0]
    ),

    'biblestudyCstm' => Array (
        'value' => $ACTIVITIES[1]
    ),

    'christcourseCstm' => Array (
        'value' => $ACTIVITIES[2]
    ),

    'homesCstm' => Array (
        'value' => $ACTIVITIES[3]
    ),

    'freebibleCstm' => Array (
        'value' => $ACTIVITIES[4]
    ),

    'description' => ''//some desc;
);

// checking if the new form data is a previous contact or lead
// searching leads first
$lead = contact_search($firstname, $lastname, $email, ZURMO_URL_LEAD_SEARCH);

if ($lead == null) {
    // searching contacts second
    $contact = contact_search($firstname, $lastname, $email, ZURMO_URL_CONTACT_SEARCH);

    if ($contact == null) {

        if ($form == "members"){
            $leadDetails['account'] = Array( 'id' => '3' );
            create_contact($leadDetails,ZURMO_URL_CONTACT_ADD);
        }else {
            create_contact($leadDetails,ZURMO_URL_LEAD_ADD);
        }

        $_SESSION['response_msg_title'] = $NEWCONTACT_RESPONSE_TITLE;
        $_SESSION['response_msg'] = $NEWCONTACT_RESPONSE;
        header("Location: registration_response.php");
    } else {
        // contact already exists
        // add a note to contacts
        $_SESSION['response_msg_title'] = $EXISTINGCONACT_REPSONSE_TITLE;
        $_SESSION['response_msg'] = $EXISTINGCONACT_REPSONSE;
        header("Location: registration_response.php");
    }

} else {
    // lead already exists
    // add a note to leads
    $_SESSION['response_msg_title'] = $EXISTINGCONACT_REPSONSE_TITLE;
    $_SESSION['response_msg'] = $EXISTINGCONACT_REPSONSE;
    header("Location: registration_response.php");
}
