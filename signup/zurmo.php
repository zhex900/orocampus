<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 12/02/2015
 * Time: 9:22 PM
 */

/**
 * Class ApiRestHelper
 * Zurmo API authentication
 * Source: http://zurmo.org/wiki/rest-api-specification-apiresthelper-class
 */

// zurmo url
define("ERROR_LOG", "/tmp/error.log");
define("ZURMO_URL", "http://zurmo.christianstudentscurtin.com/app/");
define("ZURMO_URL_LOGIN", ZURMO_URL . "index.php/zurmo/api/login");
define("ZURMO_URL_CONTACT_SEARCH", ZURMO_URL . "index.php/contacts/contact/api/list/filter/");
define("ZURMO_URL_LEAD_SEARCH", ZURMO_URL . "index.php/leads/contact/api/list/filter/");
define("ZURMO_URL_LEAD_ADD", ZURMO_URL . "index.php/leads/contact/api/create/");
define("ZURMO_URL_CONTACT_ADD", ZURMO_URL . "index.php/contacts/contact/api/create/");

// starting a session to enable session variables to be stored
session_start();

class ApiRestHelper
{
    public static function createApiCall($url, $method, $headers, $data = array())
    {
        if ($method == 'PUT') {
            $headers[] = 'X-HTTP-Method-Override: PUT';
        }

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'PUT':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'DELETE':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        $response = curl_exec($handle);
        return $response;
    }
}

/**
 * @param $username
 * @param $password
 * @return bool
 * Zurmo API authentication.
 * Source: http://zurmo.org/wiki/rest-api-specification-authentication
 *
 * Attempts to log into zurmo using the username and password given.
 * On successful login the zurmo sessionID and token are stored as
 * session variables and true is returned, otherwise false is returned.
 */
function login($username, $password)
{
    $headers = array(
        'Accept: application/json',
        'ZURMO_AUTH_USERNAME: ' . $username,
        'ZURMO_AUTH_PASSWORD: ' . $password,
        'ZURMO_API_REQUEST_TYPE: REST',
    );
    $response = ApiRestHelper::createApiCall(ZURMO_URL_LOGIN, 'POST', $headers);
    $response = json_decode($response, true);

   // file_put_contents("/tmp/error.log", "auth:(" . $username . ") (" . $password . ")", FILE_APPEND);

    if ($response['status'] == 'SUCCESS') {
        // store the access token and session ID
        $_SESSION['sessionID'] = $response['data']['sessionId'];
        $_SESSION['token'] = $response['data']['token'];
        getCustomField();
        $success = true;
        file_put_contents("/tmp/error.log", "\nauth success", FILE_APPEND);
    } else {
        $success = false;
    }
    file_put_contents("/tmp/error.log", "\nauth:" . $username ." response:" . $response['status'], FILE_APPEND);

    return $success;
}

/**
 * @param $firstname
 * @param $lastname
 * @param $email
 * @return null
 *
 * find the contact using the search parameters.
 */
function contact_search($firstname, $lastname, $email, $search_type)
{

    echo $firstname;
    echo $lastname;
    echo $email;

    $headers = array(
        'Accept: application/json',
        'ZURMO_SESSION_ID: ' . $_SESSION['sessionID'],
        'ZURMO_TOKEN: ' . $_SESSION['token'],
        'ZURMO_API_REQUEST_TYPE: REST',
    );

    $searchParams = array(
        'dynamicSearch' => array(
            'dynamicClauses' => array(
                '0' => array(
                    'attributeIndexOrDerivedType' => 'name',
                    'structurePosition' => 1,
                    'firstName' => "$firstname"
                ),
                '1' => array(
                    'attributeIndexOrDerivedType' => 'name',
                    'structurePosition' => 2,
                    'lastName' => "$lastname"
                ),
                '2' => array(
                    'attributeIndexOrDerivedType' => 'primaryEmail',
                    'structurePosition' => 3,
                    'primaryEmail' => array('emailAddress' => "$email")
                ),
            ),
            'dynamicStructure' => '1 AND 2 AND 3'

        ),
        'sort' => 'lastName.asc',
    );

    // Get first page of results
    $response = ApiRestHelper::createApiCall($search_type, 'POST', $headers, array('data' => $searchParams));

    $response = json_decode($response, true);

    if ($response['status'] == 'SUCCESS') {
        // Do something with results
        if ($response['data']['totalCount'] > 0) {
            foreach ($response['data']['items'] as $item) {
                return $item;
            }
        } else {

            // There are no contacts
            //  echo "NO CONTACTS FIND";
            return null;

        }
    } else {
        //$errors = $response['errors'];
        header("Location: error.html");
        return "ERROR";
        // Do something with errors
    }
}

function create_contact($contactDetails, $leadorcontact)
{
    $headers = array(
        'Accept: application/json',
        'ZURMO_SESSION_ID: ' . $_SESSION['sessionID'],
        'ZURMO_TOKEN: ' . $_SESSION['token'],
        'ZURMO_API_REQUEST_TYPE: REST',
    );

    $response = ApiRestHelper::createApiCall($leadorcontact, 'POST', $headers, array('data' => $contactDetails));
    $response = json_decode($response, true);
    //file_put_contents("/tmp/error.log", $response . "\n" . serialize($contactDetails) . "\n\n", FILE_APPEND);
    if ($response['status'] == 'SUCCESS') {

    } else {
        file_put_contents(ERROR_LOG, "ERROR:(" . $response . ")\n[" . serialize($contactDetails) . "]\n\n", FILE_APPEND);
        //header("Location: error.html");
    }
}

function getCustomField()
{
    // get zurmo authorisation data
    $headers = array(
        'Accept: application/json',
        'ZURMO_SESSION_ID: ' . $_SESSION['sessionID'],
        'ZURMO_TOKEN: ' . $_SESSION['token'],
        'ZURMO_API_REQUEST_TYPE: REST',
    );

    $response = ApiRestHelper::createApiCall(ZURMO_URL . 'index.php/zurmo/customField/api/list/' . $id, 'GET', $headers);
    $response = json_decode($response, true);

    if ($response['status'] == 'SUCCESS') {
        //echo "Course SUCCES
       // $customField = $response['data'];

    } else {
        file_put_contents(ERROR_LOG, "COURSE ERROR:(" . $response . ")\n", FILE_APPEND);
        // Error, for example if we provided invalid CustomFied name
       // $errors = $response['errors'];
        // Do something with errors
    }
}
