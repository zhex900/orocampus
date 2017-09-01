<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 23/8/17
 * Time: 1:58 PM
 */
require_once __DIR__ . '/vendor/autoload.php';

session_start();

define("URL", "https://orocampus.tk/app.php/api/");
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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class orocampus
{
    protected $_url;
    protected $_username;
    protected $_apiKey;
    protected $request;
    protected $session;
    protected $logger;

    public function __construct($url, $username, $apiUserKey,$session,$request)
    {
        $this->_url = $url;
        $this->_username = $username;
        $this->_apiKey = $apiUserKey;
        $this->session = $session;
        $this->request = $request;

        $this->logger =  new Logger('logger');
        $this->logger->pushHandler(new StreamHandler(__DIR__.'/log/signup.log', Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());
    }

    public function getLogger(){
        return $this->logger;
    }

    public function getAddress($contact_id)
    {
        // make the whole address in line one if the address is not find in Google map.
        if ($this->request['street_number'] == '') {
            $street = ucwords(strtolower($this->request['address']));
        } else {
            $street = $this->request['street_number'] . " " . $this->request['street_name'];
        }
        $country = "AU"; //default country code
        if (isset($this->session['dataCache']['countries'][$this->request['country']])) {
            $country = $this->session['dataCache']['countries'][$this->request['country']];
        }

        $address = [
            'data' => [
                'type' => 'contactaddresses',
                'attributes' => [
                    'primary' => true,
                    'label' => 'Primary Address',
                    'street' => $street,
                    'city' => $this->request['city'],
                    'postalCode' => $this->request['postal_code']
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
        if ($this->request['country'] == 'Australia') {
            $address['data']['relationships']['region'] =
                [
                    'data' => [
                        'type' => 'regions',
                        'id' => 'AU-' . $this->request['state']]
                ];
        }

        return $address;
    }

    public function getContact()
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

        $phones = [['phone' => $this->request['mobile']]];
        // add additional telephone number
        if (isset($this->request['telephone'])) {
            $phones = array_merge($phones, [['phone' => $this->request['telephone']]]);
        }

        $contact = [
            'data' => [
                'type' => 'contacts',
                'attributes' => [
                    'firstName' => ucwords(strtolower($this->request['fname'])),
                    'lastName' => ucwords(strtolower($this->request['lname'])),
                    'gender' => $this->request['gender'],
                    'primaryEmail' => strtolower($this->request['email']),
                    'primaryPhone' => $this->request['mobile'],
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
                            'id' => $this->session['contactSource']
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
        if (isset($this->request['countryorigin'])) {
            $contact['data']['relationships']['country_of_birth'] =
                [
                    'data' => [
                        'type' => 'countries',
                        'id' => $this->request['countryorigin']
                    ]
                ];
        }
        if (isset($this->request['course'])) {
            $contact['data']['relationships']['degrees'] =
                [
                    'data' => [
                        'type' => 'degreessources',
                        'id' => $this->request['course']
                    ]
                ];
        }
        if (isset($this->request['uni'])) {
            $contact['data']['relationships']['institutions'] =
                [
                    'data' => [
                        'type' => 'institutionssources',
                        'id' => $this->request['uni']
                    ]
                ];
        }
        if (isset($this->request['degree'])) {
            $contact['data']['relationships']['level_of_study'] =
                [
                    'data' => [
                        'type' => 'levelofstudysources',
                        'id' => $this->request['degree']
                    ]
                ];
        }

        if (isset($this->request['student_id'])) {
            $contact['data']['attributes']['student_id'] = $this->request['student_id'];
        }
        if (isset($this->request['int_student'])) {
            $contact['data']['attributes']['int_student'] = $this->request['int_student'];
        }
        if (isset($this->request['churchkid'])) {
            $contact['data']['attributes']['church_kid'] = $this->request['churchkid'];
        }
        if (isset($this->request['areyouchristian'])) {
            $contact['data']['attributes']['christian'] = $this->request['areyouchristian'];
        }
        // Feedback section
        if (isset($this->request['enquiries'])) {
            foreach ($this->request['enquiries'] as $i => $feedback) {
                $contact['data']['attributes'][$feedback] = '1';
            }
        }
        if ($this->request['dob'] != "") {
            $dob = date_format(date_create_from_format('d/m/Y', $this->request['dob']), 'Y-m-d');
            $contact['data']['attributes']['birthday'] = $dob;
        }
        return $contact;
    }

    /*
     * return if successful return created contact id otherwise return null
     */
    public function createContact($contact)
    {
        $result = $this->curl_req(CONTACT, $contact);

        if (isset($result['data']['id'])) {
            return $result['data']['id'];
        } else {
            return null;
        }
    }

    /*
     * return if successful return created contact address id otherwise return null
     */
    public function addAddress($contact_id)
    {
        //add address to the new contact
        $result = $this->curl_req(ADDRESS, $this->getAddress($contact_id));
        if (isset($result['data']['id'])) {
            return $result['data']['id'];
        } else {
            return null;
        }
    }

    /*
     * return contact id with matching email address, otherwise return null
     */
    public function findContactByEmail()
    {
        if (!isset($this->request['email'])) {
            return null;
        }
        $result = $this->curl_req(CONTACT . '?filter[primaryEmail]=' . $this->request['email']);
        if (isset($result['data'][0]['id'])) {
            return $result['data'][0]['id'];
        } else {
            return null;
        }
    }

    public function curl_req($path, $data = array())
    {
        $request = new \cURL\Request($this->_url . $path);
        $request->getOptions()
            ->set(CURLOPT_TIMEOUT, 5)
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_HEADER,false)
            ->set(CURLOPT_VERBOSE,true)
            ->set(CURLOPT_USERAGENT,'curl/7.54.0')
            ->set(CURLOPT_HTTPHEADER, $this->getHeader());

        if( !empty($data) ) {
            $request->getOptions()
                ->set(CURLOPT_POSTFIELDS, json_encode($data))
                ->set(CURLOPT_SAFE_UPLOAD, true);
        }

        $response = $request->send();
        $feed = json_decode($response->getContent(), true);

        return $feed;
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
}
