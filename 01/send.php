<?php

require_once("../vendor/autoload.php");

// Incluindo a biblioteca e as classes necessárias:
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Criando uma conexão com o servidor:
$connection = new AMQPStreamConnection("rabbitmq", 5672, "guest", "guest");

$channel = $connection->channel();

// Criando um canal (a fila só será declarada se ela não existir):
$channel->queue_declare("hello", false, false, false, false);

// Publicando a mensagem:
$message = new AMQPMessage("Hello World!");
$channel->basic_publish($message, "", "hello");

echo "[x] Sent Hello World \n";

// Fechando o canal e a conexão:
$channel->close();
$connection->close();
