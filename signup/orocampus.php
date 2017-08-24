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
define("URL", "http://app1.orocampus.tk/app.php/api/");
define("CONTACT_SEARCH", "");
define("CONTACT_ADD", "contacts");
define("CONTACT_TEST", "contacts/1");
define("APIKEY", "e59c3f6071ebcbe71b5e4bc0f9f588f5c7c03281");
define("LOGIN","admin");
//
// starting a session to enable session variables to be stored
//session_start();

$api = new ApiRestHelper(URL,LOGIN,APIKEY);

$new_contact =
    [
        'data' => [
            'type'       => 'contacts',
            'attributes' => [
                'firstName' => 'May',
                'lastName' => 'Pie',
                'gender' => 'female',
                'primaryPhone' => '0421169154',
                'primaryEmail' => 'mayie@gmail.com',
                'birthday' => '1995-01-25'
            ],
            'relationships' => [
                'owner' => [
                    'data' => [
                        'type' => 'users',
                        'id'=> '1'
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
$result = $api->curl_req(CONTACT_ADD,null,$new_contact);
//var_dump($result);
/*
 * @return bool
 * Check if the orocampus.tk API is alive.
 */
function isAPIUp(){
    /** @var ApiRestHelper $api */
    $api = new ApiRestHelper(URL,LOGIN,APIKEY);
    return !empty($api->curl_req(CONTACT_TEST));
}

class ApiRestHelper
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
        var_dump($wsseHeader);
        return $wsseHeader;
    }

    public function curl_req($path, $verb=NULL, $data=array()) {



        $wsseHeader[] = "Content-type:application/vnd.api+json";
        $wsseHeader[] = "Accept: application/json";
        $wsseHeader = array_merge($wsseHeader,$this->getHeader());

        $request = new \cURL\Request($this->_url . $path);
        $request->getOptions()
            ->set(CURLOPT_TIMEOUT, 5)
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_HTTPHEADER, $wsseHeader)
            ->set(CURLOPT_HEADER,false)
            ->set(CURLOPT_VERBOSE,true)
            ->set(CURLOPT_USERAGENT,'curl');

        if( !empty($data) ) {
            $request->getOptions()
                ->set(CURLOPT_POSTFIELDS, json_encode($data))
                ->set(CURLOPT_SAFE_UPLOAD, true);
        }
        if( isset($verb) ) {
            $request->getOptions()
                ->set(CURLOPT_CUSTOMREQUEST, $verb);
        }
        $response = $request->send();
        $feed = json_decode($response->getContent(), true);
        $result = $feed;

/*
        $options = array(
            CURLOPT_URL => $this->_url . $path,
            CURLOPT_HTTPHEADER => $wsseHeader,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_VERBOSE => true,
            CURLOPT_USERAGENT => 'curl'
        );

        if( !empty($data) ) {
            $options += array(
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_SAFE_UPLOAD => true
            );
        }

        if( isset($verb) ) {
            $options += array(CURLOPT_CUSTOMREQUEST => $verb);
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);


        if(false === $result ) {
            echo curl_error($ch);
        }

        curl_close($ch);*/
        return $result;
    }
}
