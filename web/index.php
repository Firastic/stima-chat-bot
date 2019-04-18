<?php
require __DIR__ . '/../vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
// set false for production
$pass_signature = false;
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
$app->get('/a', function($req, $res)
{
  	echo "Welcome at Slim Framework";
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
    
                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                }
            
            }
        }
        $response->write('OK');
        return $response;
    }
});

function processMessage($message, $user_id) {
    if (strpos($message,"!hi") !== false) {

        $reply_text = "Hello!";
        return $reply_text;
    }

    $reply_text = "Maaf, masukan tidak dikenali. Masukan input sesuai format";
    return $reply_text;
}

$app->run();