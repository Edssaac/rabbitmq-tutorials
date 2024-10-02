<?php

require_once("./vendor/autoload.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// Define a mensagem que será enviada
$messageBody = implode(' ', array_slice($argv, 1));

if (empty($messageBody)) {
    $messageBody = 'Mensagem de exemplo';
}

$message = new AMQPMessage($messageBody);

$channel->basic_publish($message, 'exg.p_main');

echo 'Mensagens enviadas.' . PHP_EOL;

$channel->close();
$connection->close();
