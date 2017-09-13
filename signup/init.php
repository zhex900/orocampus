<?php
/**
 * Created by PhpStorm.
 * User: zhex900
 * Date: 10/02/2015
 * Time: 3:40 PM
 */

include("orocampus.php");

/** @var orocampus $api */
$api = new orocampus(URL,
    LOGIN,
    APIKEY,'','');

if (isset($_POST['reload'])) {
    // download selection values from server
    $result = $api->get(SOURCE);
    $result = array_merge($result, $api->get(COUNTRIES));
    $result = array_merge($result, $api->get(DEGREES));
    $result = array_merge($result, $api->get(INSTITUTIONS));
    $result = array_merge($result, $api->get(LEVELOFSTUDY));

    // Write over data.json if $result is success
    if (isset($result)) {
        file_put_contents('./data/data.json', json_encode($result));
        header("Location: login.php");
    }else{
        $api->getLogger()->info('Reload details failed');
        header("Location: error.html");
    }
}

if (isset($_POST['reload-events'])) {

    $result = $api->getTodayEvent();
    // Write over data.json if $result is success
    if (isset($result)) {
        file_put_contents('./data/events.json', json_encode($result));
        header("Location: login.php");
    }else{
        $api->getLogger()->info('Reload details failed');
        header("Location: error.html");
    }
}

if (isset($_POST['login'])) {

    $formType = $_REQUEST['form_types'];
    $_SESSION['form'] = "forms/" . $formType;
    $_SESSION['contactSource'] = $_REQUEST['source_of_contact'];
    $_SESSION['event'] = $_REQUEST['event'];

    $owner = $api->getUserIdbyUsername('web');
    if (isset($owner)) {
        $_SESSION['owner'] = (string)$owner;
    }else{
        $_SESSION['owner'] = '1';
    }

    $api->getLogger()->info('selected source: '. $_SESSION['contactSource']);
    $api->getLogger()->info('selected event: '. $_SESSION['eventCache'][$_SESSION['event']]);
    // redirect to the selected form.
    header("Location: forms/" . $formType);
}
