<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 23/8/17
 * Time: 1:58 PM
 */
require_once __DIR__ . '/vendor/autoload.php';

// zurmo url
define("ERROR_LOG", "/tmp/error.log");
define("URL", "http://orocampus.tk/app.php/api/");
define("CONTACT_SEARCH", "");
define("ADDRESS", "contactaddresses");
define("CONTACT", "contacts");
define("COUNTRIES", "countries");
define("SOURCE", "contactsourcesources");
define("CONTACT_TEST", "contacts/50");
define("APIKEY", "17c9b0c24553e91b8ed84235ca4808327fbcf1c9");
define("LOGIN","system");
//
// starting a session to enable session variables to be stored
//session_start();

$api = new ApiRest(URL,LOGIN,APIKEY);


$new_contact =
    [
        'data' => [
            'type'       => 'contacts',
            'attributes' => [
                'firstName' => 'Rece',
                'lastName' => 'Li',
                'gender' => 'male',
                'primaryEmail' => '34343@gmail.com',
                'birthday' => '1995-01-21',
                'student_id' => '3124343',
                'christian' => 1,
                'int_student' => 1,
                'phones' => [['phone'=>'0403169154']],
                'primaryPhone' => '0403169154'
            ],
            'relationships' => [
                'country_of_birth' => [
                    'data' => [
                        'type' => 'countries',
                        'id'  => 'AU'
                    ]
                ],
                'degrees' => [
                    'data' => [
                        'type' => 'degreessources',
                        'id'  => 'commerce_arts'
                    ]
                ],
                'institutions' => [
                    'data' => [
                        'type' => 'institutionssources',
                        'id'  => 'university_of_new_south_wales'
                    ]
                ],
                'level_of_study' => [
                    'data' => [
                        'type' => 'levelofstudysources',
                        'id'  => 'bachelor'
                    ]
                ],
                'owner' => [
                    'data' => [
                        'type' => 'users',
                        'id'=> '12'
                    ]
                ]
            ],
            'organization' => [
                'data' => [
                    'type' => 'organizations',
                    'id'=> '1'
                ]
            ]
        ]
    ];

/*$result = $api->curl_req(SOURCE);

foreach ($result['data'] as $item){
    $array[$item['attributes']['name']] =$item['id'];
}
var_dump(json_encode($array));
*/
//var_dump($result);
$contact_id = 1 ; //$result['data']['id'];
$new_address =
    [
        'data' => [
            'type'       => 'contactaddresses',
            'attributes' => [
                'primary' => true,
                'label' => 'Primary Address',
                'street' => '1475 Hello Drive',
                'city' => 'Dallas',
                'postalCode' => '04759'
            ],
            'relationships' => [
                'owner' => [
                    'data' => [
                        'type' => 'contacts',
                        'id'  => $contact_id
                    ]
                ],
                'country' => [
                    'data' => [
                        'type' => 'countries',
                        'id'  => 'AU'
                    ]
                ]
            ]
        ]
    ];
//$result = $api->curl_req(CONTACT, $new_contact);
//var_dump($result);

//$countries=null;
//foreach ($result['data'] as $country){
//    $countries[$country['attributes']['name']] =$country['id'];
//}
//$_SESSION['Countries'] =$countries;
//var_dump(json_encode($countries));
/*
 * @return bool
 * Check if the orocampus.tk API is alive.
 */
function isAPIUp(){
    /** @var ApiRestHelper $api */
    $api = new ApiRestHelper(URL,LOGIN,APIKEY);
    return !empty($api->curl_req(CONTACT_TEST));
}
/*
 * @param array $contactDetails
 * @return int
 * returns contact id if fails return null
 */
function createContact($contactDetails){

}

class ApiRestTest
{
    protected $_url;
    protected $_username;
    protected $_apiKey;

    public function __construct($url, $username, $apiUserKey) {
        $this->_url = $url;
        $this->_username = $username;
        $this->_apiKey = $apiUserKey;
    }

    private function getHeader() {
        $nonce = base64_encode(substr(md5(uniqid()), 0, 16));;
        $created = date('c');
        $digest = base64_encode(sha1(base64_decode($nonce) . $created . $this->_apiKey, true));

        $wsseHeader[] = "Authorization: WSSE profile=\"UsernameToken\"";
        $wsseHeader[]= sprintf(
            'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', $this->_username, $digest, $nonce, $created
        );
        $wsseHeader[] = "Content-type:application/vnd.api+json";
        $wsseHeader[] = "Accept: application/json";
        var_dump($wsseHeader);
        return $wsseHeader;
    }

    public function curl_req($path, $data=array()) {

        $request = new \cURL\Request($this->_url . $path);
        $request->getOptions()
            ->set(CURLOPT_TIMEOUT, 5)
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_HTTPHEADER, $this->getHeader())
            ->set(CURLOPT_HEADER,false)
            ->set(CURLOPT_VERBOSE,true)
            ->set(CURLOPT_USERAGENT,'curl');

        if( !empty($data) ) {
            $request->getOptions()
                ->set(CURLOPT_POSTFIELDS, json_encode($data))
                ->set(CURLOPT_SAFE_UPLOAD, true);
        }

        $response = $request->send();
        $feed = json_decode($response->getContent(), true);

        return $feed;
    }


}
