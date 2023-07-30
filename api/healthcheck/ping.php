<?php
header('Content-Type: application/json');

class Message
{
    public $id;
    public $messageText;
}

class Response
{
    public $response;
    public $messages;
    public $clientIP;
}

function getUserIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$response = new Response();

$response->response = "Pong";

$response->clientIP=getUserIpAddr();

$message1 = new Message();
$message1->id = 1;
$message1->messageText = "Dave Was Here";

$message2 = new Message();
$message2->id = 2;
$message2->messageText = "Lorem, ipsum dolor sit amet consectetur adipisicing elit. Expedita, quisquam fugiat quidem porro inventore voluptate velit, voluptatum cum amet blanditiis ab facere dolores, eligendi magni iure mollitia ratione? Voluptates, non?;";

$response->messages = array($message1, $message2);


$jsonData = json_encode($response);

echo $jsonData . "\n";
