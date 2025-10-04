<?php

require_once("../vendor/autoload.php");

// Incluindo a biblioteca e as classes necessárias:
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Criando uma conexão com o servidor:
$connection = new AMQPStreamConnection("rabbitmq", 5672, "guest", "guest");

$channel = $connection->channel();

// Criando um canal (a fila só será declarada se ela não existir):
$channel->queue_declare("task_queue", false, true, false, false);

// Pegando os dados passados pela linha de comando:
$data = implode(" ", array_slice($argv, 1));

if (empty($data)) {
    $data = "Hello World!";
}

// Publicando a mensagem:
$message = new AMQPMessage(
    $data,
    [
        "delivery_mode" => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]
);

$channel->basic_publish($message, "", "task_queue");

echo "[x] Sent $data\n";

// Fechando o canal e a conexão:
$channel->close();
$connection->close();
