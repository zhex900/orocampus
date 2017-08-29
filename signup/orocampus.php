<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 23/8/17
 * Time: 1:58 PM
 */
require_once __DIR__ . '/vendor/autoload.php';

session_start();

define("URL", "http://orocampus.tk/app.php/api/");
define("DEGREES", 'degreessources');
define("INSTITUTIONS", 'institutionssources');
define("LEVELOFSTUDY", 'levelofstudysources');
define("ADDRESS", "contactaddresses");
define("CONTACT", "contacts");
define("COUNTRIES", "countries");
define("SOURCE", "contactsourcesources");
define("CONTACT_TEST", "contacts/50");
define("APIKEY", "10a8c562829409f64174386c8400deb30223436f");
define("LOGIN", "system");
define("LOGIN_ID", "12");


function getAddress($contact_id)
{
    // make the whole address in line one if the address is not find in Google map.
    if ($_REQUEST['street_number'] == '') {
        $street = ucwords(strtolower($_REQUEST['address']));
    } else {
        $street = $_REQUEST['street_number'] . " " . $_REQUEST['street_name'];
    }
    $country="AU"; //default country code
    if(isset($_SESSION['dataCache']['countries'][$_REQUEST['country']])){
        $country= $_SESSION['dataCache']['countries'][$_REQUEST['country']];
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
                        'id' => $country
                    ]
                ]
            ]
        ]
    ];
    // Add Australian region (state)
    if ($_REQUEST['country'] == 'Australia') {
        $address['data']['relationships']['region'] =
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
    /*
     * Compulsory fields
     * mobile
     * fname
     * lname
     * gender
     * email
     * contactSource
     */

    $phones = [['phone' => $_REQUEST['mobile']]];
    // add additional telephone number
    if (isset($_REQUEST['telephone'] )) {
        $phones = array_merge($phones, [['phone' => $_REQUEST['telephone']]]);
    }

    $contact = [
        'data' => [
            'type' => 'contacts',
            'attributes' => [
                'firstName' => ucwords(strtolower($_REQUEST['fname'])),
                'lastName' => ucwords(strtolower($_REQUEST['lname'])),
                'gender' => $_REQUEST['gender'],
                'primaryEmail' => strtolower($_REQUEST['email']),
                'primaryPhone' => $_REQUEST['mobile'],
                'phones' => $phones,
            ],
            'relationships' => [
                'owner' => [
                    'data' => [
                        'type' => 'users',
                        'id' => LOGIN_ID
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

    /*
     * Optional fields
     */
    if (isset($_REQUEST['countryorigin'])) {
        $contact['data']['relationships']['country_of_birth'] =
            [
                'data' => [
                    'type' => 'countries',
                    'id' => $_REQUEST['countryorigin']
                ]
            ];
    }
    if (isset($_REQUEST['course'])) {
        $contact['data']['relationships']['degrees'] =
            [
                'data' => [
                    'type' => 'degreessources',
                    'id' => $_REQUEST['course']
                ]
            ];
    }
    if (isset($_REQUEST['uni'])) {
        $contact['data']['relationships']['institutions'] =
            [
                'data' => [
                    'type' => 'institutionssources',
                    'id' => $_REQUEST['uni']
                ]
            ];
    }
    if (isset($_REQUEST['degree'])) {
        $contact['data']['relationships']['level_of_study'] =
            [
                'data' => [
                    'type' => 'levelofstudysources',
                    'id' => $_REQUEST['degree']
                ]
            ];
    }

    if (isset($_REQUEST['student_id'])) {
        $contact['data']['attributes']['student_id'] = $_REQUEST['student_id'];
    }
    if (isset($_REQUEST['int_student'])) {
        $contact['data']['attributes']['int_student'] = $_REQUEST['int_student'];
    }
    if (isset($_REQUEST['churchkid'])) {
        $contact['data']['attributes']['church_kid'] = $_REQUEST['churchkid'];
    }
    if (isset($_REQUEST['areyouchristian'])) {
        $contact['data']['attributes']['christian'] = $_REQUEST['areyouchristian'];
    }
    // Feedback section
    if (isset($_REQUEST['enquiries'])) {
        foreach ($_REQUEST['enquiries'] as $i => $feedback) {
            $contact['data']['attributes'][$feedback] = '1';
        }
    }
    if ($_REQUEST['dob']!="") {
        $dob = date_format(date_create_from_format('d/m/Y', $_REQUEST['dob']), 'Y-m-d');
        $contact['data']['attributes']['birthday'] = $dob;
    }
    return $contact;
}

/*
 * return if successful return created contact id otherwise return null
 */
function createContact($contact)
{
    $api = new ApiRest(URL, LOGIN, APIKEY);
    $result = $api->curl_req(CONTACT, $contact);

    if (isset($result['data']['id'])) {
        return $result['data']['id'];
    } else {
        return null;
    }
}

/*
 * return if successful return created contact address id otherwise return null
 */
function addAddress($contact_id)
{
    $api = new ApiRest(URL, LOGIN, APIKEY);
    //add address to the new contact
    $result = $api->curl_req(ADDRESS, getAddress($contact_id));
    if (isset($result['data']['id'])) {
        return $result['data']['id'];
    } else {
        return null;
    }
}

/*
 * return contact id with matching email address, otherwise return null
 */
function findContactByEmail()
{
    if (!isset($_REQUEST['email'])) {
        return null;
    }
    $api = new ApiRest(URL, LOGIN, APIKEY);
    $result = $api->curl_req(CONTACT . '?filter[primaryEmail]=' . $_REQUEST['email']);
    if (isset($result['data'][0]['id'])) {
        return $result['data'][0]['id'];
    } else {
        return null;
    }
}

class ApiRest
{
    protected $_url;
    protected $_username;
    protected $_apiKey;

    public function __construct($url, $username, $apiUserKey)
    {
        $this->_url = $url;
        $this->_username = $username;
        $this->_apiKey = $apiUserKey;
    }

    private function getHeader()
    {
        $nonce = base64_encode(substr(md5(uniqid()), 0, 16));;
        $created = date('c');
        $digest = base64_encode(sha1(base64_decode($nonce) . $created . $this->_apiKey, true));

        $wsseHeader[] = "Content-type:application/vnd.api+json";
        $wsseHeader[] = "Accept: application/json";
        $wsseHeader[] = "Authorization: WSSE profile=\"UsernameToken\"";
        $wsseHeader[] = sprintf(
            'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', $this->_username, $digest, $nonce, $created
        );
        // var_dump($wsseHeader);
        return $wsseHeader;
    }

    public function curl_req($path, $data = array())
    {

        $request = new \cURL\Request($this->_url . $path);
        $request->getOptions()
            ->set(CURLOPT_TIMEOUT, 5)
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_HEADER, false)
            ->set(CURLOPT_VERBOSE, true)
            ->set(CURLOPT_USERAGENT, 'curl/7.54.0')
            ->set(CURLOPT_HTTPHEADER, $this->getHeader());

        if (!empty($data)) {
            $request->getOptions()
                ->set(CURLOPT_POSTFIELDS, json_encode($data))
                ->set(CURLOPT_SAFE_UPLOAD, true);
        }

        $response = $request->send();
        $feed = json_decode($response->getContent(), true);

        return $feed;
    }
}
