<?php

require_once("../vendor/autoload.php");

// Incluindo a biblioteca e as classes necessárias:
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Abrindo a conexão:
$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

$channel = $connection->channel();

// Criando uma Exchange:
$channel->exchange_declare('logs', 'fanout', false, false, false);

// Declarando um nome aleatório para a fila temporária:
list($queue_name,,) = $channel->queue_declare("", false, false, true, false);

// Vinculando a fila com a Exchange:
$channel->queue_bind($queue_name, 'logs');

echo " [*] Waiting for logs. To exit press CTRL+C\n";

// Callback para exibir a mensagem consumida:
$callback = function (AMQPMessage $message) {
    echo ' [x] ', $message->getBody(), "\n";
};

// Consumindo a fila:
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

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
php 03/receive_logs.php > App/logs_from_rabbit.log
php 03/receive_logs.php

php 03/emit_log.php teste
*/
