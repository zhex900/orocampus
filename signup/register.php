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

// user information
$firstname = ucwords ( strtolower ($_REQUEST['fname'] ));

function getAddress($contact_id)
{
    // make the whole address in line one if the address is not find in Google map.
    if ($_REQUEST['street_number']=='') {
        $street = ucwords ( strtolower ($_REQUEST['address']));
    }else{
        $street = $_REQUEST['street_number'] . " ". $_REQUEST['street_name'];
    }

    if (empty($_SESSION['countries'])){
        $_SESSION['countries'] = json_decode(file_get_contents("./data/countries.json"), true);
    }

    $address = [
        'data' => [
            'type' => 'contactaddresses',
            'attributes' => [
                'primary' => true,
                'label' => 'Primary Address',
                'street' => $street,
                'city' => $_REQUEST['city'],
                'postalCode' => $_REQUEST['postal_code']
            ],
            'relationships' => [
                'owner' => [
                    'data' => [
                        'type' => 'contacts',
                        'id' => $contact_id
                    ]
                ],
                'country' => [
                    'data' => [
                        'type' => 'countries',
                        'id' => $_SESSION['dataCache']['countries'][$_REQUEST['country']]
                    ]
                ]
            ]
        ]
    ];
    // Add Australian region (state)
    if ($_REQUEST['country']=='Australia'){
        $address['data']['relationships']['region']=
            [
                'data' => [
                'type' => 'regions',
                'id' => 'AU-' . $_REQUEST['state']]
            ];
    }

    return $address;
}

function getContact()
{
    // add field only when it is filled.

    $phones = [['phone'=>$_REQUEST['mobile']]];
    if ($_REQUEST['telephone']!=''){
        $phones=array_merge($phones,[['phone'=>$_REQUEST['phone']]]);
    }
    //var_dump($_REQUEST['countryorigin']);
    $contact = [
        'data' => [
            'type' => 'contacts',
            'attributes' => [
                'firstName' => ucwords ( strtolower ($_REQUEST['fname'] )),
                'lastName' => ucwords ( strtolower ($_REQUEST['lname'])),
                'gender' => $_REQUEST['gender'],
                'primaryEmail' => strtolower($_REQUEST['email']),
                'student_id' => $_REQUEST['student_id'],
                'christian' => $_REQUEST['areyouchristian'],
                'int_student' => $_REQUEST['int_student'],
                'primaryPhone' => $_REQUEST['mobile'],
                'phones' => $phones,
                'church_kid' => $_REQUEST['churchkid']
            ],
            'relationships' => [
                'country_of_birth' => [
                    'data' => [
                        'type' => 'countries',
                        'id' => $_REQUEST['countryorigin']
                    ]
                ],
                'degrees' => [
                    'data' => [
                        'type' => 'degreessources',
                        'id' => $_REQUEST['course']
                    ]
                ],
                'institutions' => [
                    'data' => [
                        'type' => 'institutionssources',
                        'id' => $_REQUEST['uni']
                    ]
                ],
                'level_of_study' => [
                    'data' => [
                        'type' => 'levelofstudysources',
                        'id' => $_REQUEST['degree']
                    ]
                ],
                'owner' => [
                    'data' => [
                        'type' => 'users',
                        'id' => '12'
                    ]
                ],
                'contact_source' => [
                    'data' => [
                        'type' => 'contactsourcesources',
                        'id' => $_SESSION['contactSource']
                    ]
                ]
            ],
            'organization' => [
                'data' => [
                    'type' => 'organizations',
                    'id' => '1'
                ]
            ]
        ]
    ];
    // add DOB when it is not empty.
    if ($_REQUEST['dob']!="") {
        $dob = date_format(date_create_from_format('d/m/Y', $_REQUEST['dob']), 'Y-m-d');
        $contact['data']['attributes']['birthday']=$dob;
    }
    return $contact;
}


$NEWCONTACT_RESPONSE_TITLE = "Registration Successful!";
$EXISTINGCONACT_REPSONSE_TITLE = "Already Registered";
$NEWCONTACT_RESPONSE =  "Thank you for signing up " . $firstname . "!";
$EXISTINGCONACT_REPSONSE = "Looks like you have already registered. <br> Thanks for coming back " . $firstname . "!";

if(createContact(getContact())!=null){
    $_SESSION['response_msg_title'] = $NEWCONTACT_RESPONSE_TITLE;
    $_SESSION['response_msg'] = $NEWCONTACT_RESPONSE;
}else{
    $_SESSION['response_msg_title'] = "Registration failed";
    $_SESSION['response_msg'] = "Please try again";
}

header("Location: registration_response.php");

function createContact($contact){
    file_put_contents('/tmp/signup.log','address state: '. $_REQUEST['userstate'].PHP_EOL,FILE_APPEND);

    $api = new ApiRest(URL,LOGIN,APIKEY);
    //create new contact
    //var_dump($contact);
    $result = $api->curl_req(CONTACT, $contact);
    // check if create contact is successful
    //var_dump(print_r($result,true));
    file_put_contents('/tmp/signup.log','new contact id: '. $result['data']['id'].PHP_EOL.PHP_EOL,FILE_APPEND);
   // var_dump(getAddress($result['data']['id']));
    //add address to the new contact
    $result = $api->curl_req(ADDRESS, getAddress($result['data']['id']));
    // check if create address is successful
    return $result;
}

/*
// checking if the new form data is a previous contact or lead
// searching leads first
$lead = contact_search($firstname, $lastname, $email, ZURMO_URL_LEAD_SEARCH);

if ($lead == null) {
    // searching contacts second
    $contact = contact_search($firstname, $lastname, $email, ZURMO_URL_CONTACT_SEARCH);

    if ($contact == null) {

        if ($form == "members"){
            $contactDetails['account'] = Array( 'id' => '3' );
            create_contact($contactDetails,ZURMO_URL_CONTACT_ADD);
        }else {
            create_contact($contactDetails,ZURMO_URL_LEAD_ADD);
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
}*/
