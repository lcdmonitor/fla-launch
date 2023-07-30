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
}


$response = new Response();

$response->response = "Pong";

$message1 = new Message();
$message1->id=1;
$message1->messageText="Dave Was Here";

$message2 = new Message();
$message2->id=2;
$message2->messageText="Lorem, ipsum dolor sit amet consectetur adipisicing elit. Expedita, quisquam fugiat quidem porro inventore voluptate velit, voluptatum cum amet blanditiis ab facere dolores, eligendi magni iure mollitia ratione? Voluptates, non?;";

$response->messages=array($message1, $message2);


$jsonData = json_encode($response);

echo $jsonData . "\n";
