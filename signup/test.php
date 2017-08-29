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
define("APIKEY", "10a8c562829409f64174386c8400deb30223436f");
define("LOGIN","system");
//
// starting a session to enable session variables to be stored
//session_start();

$api = new ApiRestTest(URL,LOGIN,APIKEY);


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

//.'?filter[primaryEmail]=112@sw.com&page[number]=1&page[size]=10&sort=id');
$_REQUEST['email'] = '34343@gmail.com';
function findContactByEmail(){
    if (!isset($_REQUEST['email'])){
        return null;
    }
    $api = new ApiRestTest(URL,LOGIN,APIKEY);
    $result = $api->curl_req(CONTACT .'?filter[primaryEmail]='.$_REQUEST['email']);
    if (isset($result['data'][0]['id'])){
        return $result['data'][0]['id'];
    }else{
        return null;
    }
}
//$result = findContactByEmail();
//foreach ($result['data'] as $item){
//    $array[$item['attributes']['name']] =$item['id'];
//}

//var_dump($result);
$contact_id = 90 ; //$result['data']['id'];
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
                        'id'  => '90'
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

/*
 * return if successful return created contact id otherwise return null
 */
function createContact($contact){
    $api = new ApiRest(URL,LOGIN,APIKEY);
    $result = $api->curl_req(CONTACT, $contact);

    if (isset($result['data']['id'])){
        return $result['data']['id'];
    }else{
        return null;
    }
}

/*
 * return if successful return created contact address id otherwise return null
 */
function addAddress($contact_id){
    $api = new ApiRest(URL,LOGIN,APIKEY);
    //add address to the new contact
    $result = $api->curl_req(ADDRESS, getAddress($contact_id));
    if (isset($result['data']['id'])){
        return $result['data']['id'];
    }else{
        return null;
    }
}
$id = createContact($new_contact);
var_dump($id);

$result = $api->curl_req(ADDRESS, $new_address) ;
var_dump($result);

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

        $wsseHeader[] = "Content-type:application/vnd.api+json";
        $wsseHeader[] = "Accept: application/json";
        $wsseHeader[] = "Authorization: WSSE profile=\"UsernameToken\"";
        $wsseHeader[]= sprintf(
            'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', $this->_username, $digest, $nonce, $created
        );
        var_dump($wsseHeader);
        return $wsseHeader;
    }

    public function curl_req($path, $data=array()) {
/*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->_url . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
       // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.54.0');
       // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); //LET OP!! PATCH voor updates!!
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeader());
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
*/
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


}
