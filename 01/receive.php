<?php

require_once("./vendor/autoload.php");

// Incluindo a biblioteca e as classes necessárias:
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Abrindo a conexão:
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// Se a fila já não existir, então ela será declarada agora:
$channel->queue_declare('hello', false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// Callback para exibir a mensagem consumida:
$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
};

// Consumindo a fila:
$channel->basic_consume('hello', '', false, true, false, false, $callback);

// Tentando consumir a fila:
try {
    $channel->consume();
} catch (Throwable $exception) {
    echo $exception->getMessage();
}

// Fechando o canal e a conexão:
$channel->close();
$connection->close();

/*
php 01/receive.php
php 01/send.php
*/
