<?php

require_once("./vendor/autoload.php");

// Incluindo a biblioteca e as classes necessárias:
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Criando uma conexão com o servidor:
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// Criando uma Exchange:
$channel->exchange_declare('logs', 'fanout', false, false, false);

// Pegando os dados passados pela linha de comando:
$data = implode(' ', array_slice($argv, 1));

if (empty($data)) {
    $data = 'Hello World!';
}

// Publicando a mensagem:
$msg = new AMQPMessage(
    $data,
    [
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]
);

$channel->basic_publish($msg, 'logs');

echo ' [x] Sent ', $data, "\n";

// Fechando o canal e a conexão:
$channel->close();
$connection->close();
