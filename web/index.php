<?php
require __DIR__ . '/../vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
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
 
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  	echo "Welcome at Slim Framework";
    $array = [];
    $array = getDatabase($array);
    print_r($array);
});

$app->get('/playful', function($req, $res){
    echo '<img src="assets/playful.jpeg">';
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
                    $result = $bot->replyText($event['replyToken'], $reply_text);
                    $bot->replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => $reply_text
                                ],
                                [
                                    'type' => 'image',
                                    'originalContentUrl' => 'https://stima-chat-bot.herokuapp.com/playful',
                                    'previewImageUrl' => 'https://stima-chat-bot.herokuapp.com/playful'
                                ]
                            ]
                        ]);
                }
            }
        }
        $response->write('OK');
        return $response;
    }
});

function processMessage($message, $user_id) {
    $arr = [];
    $arr = getDatabase($arr);
    //$arr = array(array("question" => "Siapa kamu?", "answer" => "Perkenalkan, saya Saia!"));
    $reply_text = "Maaf, masukan tidak dikenali. Masukan input sesuai format";
    if (strpos($message,"!hi") !== false) {

        $reply_text = "Hello!";
    } else {
        foreach($arr as $qa){
            if(strpos($message,$qa["question"]) !== false){
                $reply_text = $qa["answer"];
            }
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
        $temp[1] = substr($temp[1], 1);
        //print_r($temp);
        array_push($array, array("question" => $temp[0], "answer" => $temp[1]));
    }
    //print_r($convertedArray[0]);
    //print_r($array[0]);
    return $array;
}

$app->run();