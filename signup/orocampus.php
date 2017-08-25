<?php
/**
 * Created by PhpStorm.
 * User: jake
 * Date: 23/8/17
 * Time: 1:58 PM
 */
require_once __DIR__ . '/vendor/autoload.php';
session_start();
define("ERROR_LOG", "/tmp/error.log");

define("URL", "http://orocampus.tk/app.php/api/");

define("DEGREES", 'degreessources');
define("INSTITUTIONS", 'institutionssources');
define("LEVELOFSTUDY", 'levelofstudysources');
define("ADDRESS", "contactaddresses");
define("CONTACT", "contacts");
define("COUNTRIES", "countries");
define("SOURCE", "contactsourcesources");

define("CONTACT_TEST", "contacts/50");

define("APIKEY", "17c9b0c24553e91b8ed84235ca4808327fbcf1c9");
define("LOGIN","system");

/*
 * @return bool
 * Check if the orocampus.tk API is alive.
 */
function isAPIUp(){
    /** @var ApiRestHelper $api */
    $api = new ApiRest(URL,LOGIN,APIKEY);
    return !empty($api->curl_req(CONTACT_TEST));
}

class ApiRest
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
       // var_dump($wsseHeader);
        return $wsseHeader;
    }

    public function curl_req($path, $data=array()) {

        $request = new \cURL\Request($this->_url . $path);
        $request->getOptions()
            ->set(CURLOPT_TIMEOUT, 5)
            ->set(CURLOPT_RETURNTRANSFER, true)
            ->set(CURLOPT_HTTPHEADER, $this->getHeader())
            ->set(CURLOPT_HEADER,false)
            ->set(CURLOPT_VERBOSE,false)
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
