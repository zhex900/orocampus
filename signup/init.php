<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 10/02/2015
 * Time: 3:40 PM
 */

include("orocampus.php");

if (isset($_POST['reload'])) {
    // download selection values from server
    $api = new ApiRest(URL, LOGIN, APIKEY);

    $result = get($api, SOURCE);
    $result = array_merge($result, get($api, COUNTRIES));
    $result = array_merge($result, get($api, DEGREES));
    $result = array_merge($result, get($api, INSTITUTIONS));
    $result = array_merge($result, get($api, LEVELOFSTUDY));
    file_put_contents('./data/data.json', json_encode($result));

    // redirect to the selected form.
    header("Location: login.php");

}

if (isset($_POST['login'])) {

    $formType = $_REQUEST['form_types'];
    $_SESSION['form'] = "forms/" . $formType;
    $_SESSION['contactSource'] = $_REQUEST['source_of_contact'];
   // $_SESSION['countries'] = json_decode(file_get_contents("./data/countries.json"), true);

    //load drop-down values
    $file = './data/data.json';
    if (!is_file($file) || !is_readable($file)) {
        die("File not accessible.");
    }
    $contents = file_get_contents($file);
    $_SESSION['dataCache'] = json_decode($contents, true);

    file_put_contents('/tmp/signup.log', 'dataCache: ' . print_r($_SESSION['dataCache'], true) . PHP_EOL, FILE_APPEND);
    file_put_contents('/tmp/signup.log', 'source: ' . $_SESSION['contactSource'] . PHP_EOL, FILE_APPEND);

    // redirect to the selected form.
    header("Location: forms/" . $formType);
}

function get($api, $source)
{
    $result = $api->curl_req($source);
    // rewrite if the result is not null
    if ($result != null) {
        //append to the data file
        return transform($source, $result);
        // TODO
        // give success notification
    } else {
        // TODO
        // give failed notification
    }
}

function transform($source, $result)
{
    $array = null;
    if (!empty($result)) {
        foreach ($result['data'] as $item) {
            $array[$source][$item['attributes']['name']] = $item['id'];
        }
    }
    return $array;
}