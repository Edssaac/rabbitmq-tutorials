<?php

require_once("./vendor/autoload.php");

// Incluindo a biblioteca e as classes necessárias:
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Abrindo a conexão:
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

// Se a fila já não existir, então ela será declarada agora:
$channel->queue_declare('task_queue', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// Callback para exibir a mensagem consumida:
$callback = function ($msg) {
    echo ' [x] Received ', $msg->getBody(), "\n";

    sleep(substr_count($msg->getBody(), '.'));

    echo " [x] Done\n";

    // Informando que a mensagem pode ser removida da fila:
    $msg->ack();
};

// Definindo limite da fila:
$channel->basic_qos(null, 1, false);

// Consumindo a fila:
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

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
php 02/worker.php

php 02/new_task.php 1 mensagem.
php 02/new_task.php 2 mensagem..
php 02/new_task.php 3 mensagem...
php 02/new_task.php 4 mensagem....
php 02/new_task.php 5 mensagem.....
*/
