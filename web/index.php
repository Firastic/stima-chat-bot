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
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "rME1fEayO7CdPqVqjslYaNFFyJ5Neg4CuNdbtQLM+i0h0l6iUL+4ruWaR+bwnMmiDVpWDIhpVHNUukYnkhj3GNCcDDcJ+iXQQ4yv9N/3JvjXoGcV9vlERksBlJ5zulvw2GErUNm094P8D4EMIagHegdB04t89/1O/w1cDnyilFU=";
$channel_secret = "636be765d69bcd149d1d68831d432e11";

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
});

$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
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
    
                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }
            
            }
        }
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