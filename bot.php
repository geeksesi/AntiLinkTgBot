<?php
// Creat by geeksesi
//   Geeksesi.xyz
//      @geeksesi_xyz
//          @geeksesi
// Simple Anti Link Telegram Bot
// 
// GNU GENERAL PUBLIC LICENSE

define('BOT_TOKEN', '! ! Bot_Token_here ! ! ');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
function apiRequestWebhook($method, $parameters)
{
    //!!! $method must be a string !!!
    if (!is_string($method))
    {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters["method"] = $method;

    header("Content-Type: application/json");
    echo json_encode($parameters);
    return true;
}


// exec $handel page source !
function exec_curl_request($handle) {
    $response = curl_exec($handle);

    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);
        return false;
    }
    //intval ==> Get the integer value of a variable
    //curl_getinfo ==> Get information regarding a specific transfer
    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);

    if ($http_code >= 500) {
        // do not wat to DDOS server if something goes wrong
        sleep(10);
        return false;
    } else if ($http_code != 200) {
        $response = json_decode($response, true);
        error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
        if ($http_code == 401) {
            throw new Exception('Invalid access token provided');
        }
        return false;
    } else {
        $response = json_decode($response, true);
        if (isset($response['description'])) {
            error_log("Request was successfull: {$response['description']}\n");
        }
        $response = $response['result'];
    }

    return $response;
}

function apiRequest($method, $parameters) {
    // $method must be  string !
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }
    //if parametr is empty make defult array
    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    foreach ($parameters as $key => &$val) {
        // encoding to JSON array parameters, for example reply_markup
        if (!is_numeric($val) && !is_string($val)) {
            $val = json_encode($val);
        }
    }
    // http_build_query ==> Generate URL-encoded query string
    $url = API_URL.$method.'?'.http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters["method"] = $method;

    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    return exec_curl_request($handle);
}

define('WEBHOOK_URL', 'https://Ostore-design.ir/bot/bot2.php');
$me_id = '91416644' ;
if (php_sapi_name() == 'cli') {
    // if run from console, set or delete webhook
    apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
    exit;
}




$me_id = '91416644' ;

// $count check update
$content = file_get_contents("php://input");
$update = json_decode($content, true);

//$update['message']['chat]['type'] ==> private or grope or channel !

if (!$update)
{
    // receive wrong update, must not happen
    exit;
}


if (isset($update["message"])) {

    // User || dont work !
    $user_id = $update["message"]["from"]["id"];
    $user_uname = $update["message"]["from"]["username"];
    $user_first_name = $update["message"]["from"]["first_name"];

    // Chat
    $chat_id = $update["message"]["chat"]["id"];
    $chat_type = $update["message"]["chat"]["type"];

    if ($chat_type != 'private') {
        $chat_title = $update["message"]["chat"]["title"];
        $chat_user_name = $update["message"]["chat"]["username"];

    }

    // Message
    $message_id = $update["message"]["message_id"];
    $message_date = $update["message"]["date"];


    if (isset($update["message"]["text"])) {
        $message_text = $update["message"]["text"];
        // entities is option from text !
        $message_entities = $update["message"]["entities"][0];
        //$update["message"]["entities"][0] is object in json array
        $message_en_type = $message_entities["type"];
        $message_en_length = $message_entities['length'];
        

        if ($chat_type != 'private') {
            //anti URL
            if ($message_en_type == 'url') {
                apiRequestWebhook(deleteMessage, array("chat_id" => $chat_id, "message_id" => $message_id));
            }

        }

    }


}

