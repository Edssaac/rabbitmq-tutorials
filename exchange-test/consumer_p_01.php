<?php

require_once("../vendor/autoload.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection("rabbitmq", 5672, "guest", "guest");

$channel = $connection->channel();

$callback = function (AMQPMessage $message) {
    $body = $message->getBody();

    echo "Projeto 1: $body" . PHP_EOL;

    $message->ack();
};

$channel->basic_consume("p_01", "", false, false, false, false, $callback);

echo "Aguardando mensagens. Para sair, pressione CTRL+C", PHP_EOL;

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
