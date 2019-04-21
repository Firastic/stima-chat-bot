<?php
require __DIR__ . '/../vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
// set false for production
$pass_signature = true;
if(getenv("PRODUCTION") !== false){
    $pass_signature = false;
} 

// set LINE channel_access_token and channel_secret
$channel_access_token = "7GQ5Dn4ioKUGVSHNmUoWLh26TrsTOd3C/T8nyzXcknzbMQbXIpZxFHylbE/nXADGpL5Ksev6WJF+GiTxkBdN8Hwsliz+9E8tTTJEaZ2zpMvRflaCFcRh7pWYxMdhHd66LpOCaxM7MXqUTJ3O4weqFgdB04t89/1O/w1cDnyilFU=";
$channel_secret = "32cf0cd5f73ae9ec84f88afda70c32b8";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
 

$GLOBALS["mode"] = 0;
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  	//echo "Welcome at Slim Framework";
    #$array = [];
    #$array = getDatabase($array);
    //print_r($array);
    //exec('cd .. && ls', $output);
    //foreach($output as $x){
    //    echo $x;
    //}
    $str = 'cd .. && python backend.py bm "Berapa jumlah SKS minimal untuk lulus S1 di ITB"';
    echo $str;
    exec('cd .. && python backend.py bm "Berapa jumlah SKS minimal untuk lulus S1 di ITB"', $output);
    var_dump($output);
});

$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $channel_secret)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);
 
    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }
 
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {
                    $user_id = $event['source']['userId'];
                    $message = $event['message']['text'];
                    $reply_text = processMessage($message, $user_id);
                    $replyMessage = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
                    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($reply_text);
                    $replyMessage->add($textMessageBuilder);
                    $emotion = "playful.jpeg";
                    if(strpos($reply_text, "ehe") !== false){
                        $emotion = "confused.jpeg";
                    } else if(strpos($message, "Siapa") !== false){
                        $emotion = "happy.jpeg";
                    } else if(strpos($message, "iwang") !== false){
                        $emotion = "confused.jpeg";
                    } else if(strpos($message, "apa") !== false){
                        $emotion = "proud.jpeg";
                    }
                    $imageMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder('https://stima-chat-bot.herokuapp.com/assets/' . $emotion, 'https://stima-chat-bot.herokuapp.com/assets/' . $emotion);
                    $replyMessage->add($imageMessageBuilder);
                    $result = $bot->replyMessage($event['replyToken'], $replyMessage);
                }
            }
        }
        $response->write('OK');
        return $response;
    }
});

function processMessage($message, $user_id) {
    $arr = [];
    $reply_text = "Maaf, aku tidak mengenali kata-katamu, coba diperjelas ehe";
    $modeStr = "kmp";
    if($GLOBALS["mode"] == 0)$modeStr = "kmp";
    else if($GLOBALS["mode"] == 1)$modeStr = "bm";
    else if($GLOBALS["mode"] == 2)$modeStr = "regex";
    if($message.lower() == "kmp"){
        $GLOBALS["mode"] = 0;
        $reply_text = "Okok aku ganti jadi KMP"
    } else if($message.lower() == "bm"){
        $GLOBALS["mode"] = 1;
        $reply_text = "Okok aku ganti jadi Boyer-Moore"
    } else if($message.lower() == "regex"){
        $GLOBALS["mode"] = 2;
        $reply_text = "Okok aku ganti jadi Regex"
    } else {
        exec('cd .. && python backend.py ' + $modeStr + ' "' . $message . '"', $output);
        if($output[0] !== "None"){
            $reply_text = $output[0];
            $reply_text = ltrim($reply_text);
        } 
    }
    return $reply_text;
}

function getDatabase($array){
    exec('cd .. && python database.py', $output);
    //echo $output[0];
    $output[0] = str_replace(", [", "", $output[0]);
    $output[0] = str_replace("[", "", $output[0]);  
    $output[0] = str_replace("\\n", "", $output[0]);
    $output[0] = str_replace("\\r", "", $output[0]);
    //print_r($output[0]);
    $convertedArray = explode(']', $output[0]);
    //print_r($convertedArray);
    foreach($convertedArray as $pair){
        if($pair == NULL)break; 
        //print_r($pair);
        $pair = str_replace("'", "", $pair);
        $temp = explode(',', $pair);
        $temp[1] = ltrim($temp[1]);
        //print_r($temp);
        array_push($array, array("question" => $temp[0], "answer" => $temp[1]));
    }
    //print_r($convertedArray[0]);
    //print_r($array[0]);
    return $array;
}

$app->run();    