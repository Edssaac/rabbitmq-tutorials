<?php

require_once("./vendor/autoload.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$channel->exchange_declare('direct_logs', 'direct', false, false, false);

list($queue_name,,) = $channel->queue_declare("", false, false, true, false);

$severities = array_slice($argv, 1);

if (empty($severities)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");

    exit(1);
}

foreach ($severities as $severity) {
    $channel->queue_bind($queue_name, 'direct_logs', $severity);
}

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] ', $msg->getRoutingKey(), ':', $msg->getBody(), "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

try {
    $channel->consume();
} catch (Throwable $exception) {
    echo $exception->getMessage();
}

$channel->close();
$connection->close();

/*
php 04/receive_logs_direct.php error > App/logs_from_rabbit.log
php 04/receive_logs_direct.php error warning info

php 04/emit_log_direct.php warning "Teste sendo testado."
*/
