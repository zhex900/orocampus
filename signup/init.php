<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 10/02/2015
 * Time: 3:40 PM
 */

include("orocampus.php");

if (isset($_POST['reload'])){
    // download selection values from server
    $api = new ApiRest(URL,LOGIN,APIKEY);

    get($api,SOURCE);
    get($api,COUNTRIES);
    get($api,DEGREES);
    get($api,INSTITUTIONS);
    get($api,LEVELOFSTUDY);
    // redirect to the selected form.
    header("Location: login.php");

}

if (isset($_POST['login'])) {

    $formType = $_REQUEST['form_types'];
    $_SESSION['form'] = "forms/" . $formType;
    $_SESSION['contactSource'] = $_REQUEST['source_of_contact'];
    $_SESSION['countries'] = json_decode(file_get_contents("./data/countries.json"), true);

    file_put_contents('/tmp/signup.log','source: '. $_SESSION['contactSource'].PHP_EOL,FILE_APPEND);

    // redirect to the selected form.
    header("Location: forms/" . $formType);
}

function get($api,$source){
    $result = $api->curl_req($source);
    file_put_contents( './data/'.$source.'.json',json_encode(transform($result)));
}

function transform($result){
    $array=null;
    if (!empty($result)) {
        foreach ($result['data'] as $item) {
            $array[$item['attributes']['name']] = $item['id'];
        }
    }
    return $array;
}