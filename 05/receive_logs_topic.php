<?php

require_once("../vendor/autoload.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection("rabbitmq", 5672, "guest", "guest");

$channel = $connection->channel();

$channel->exchange_declare("topic_logs", "topic", false, false, false);

list($queue_name,,) = $channel->queue_declare("", false, false, true, false);

$binding_keys = array_slice($argv, 1);

if (empty($binding_keys)) {
    file_put_contents("php://stderr", "Usage: $argv[0] [binding_key]\n");

    exit(1);
}

foreach ($binding_keys as $binding_key) {
    $channel->queue_bind($queue_name, "topic_logs", $binding_key);
}

echo "[*] Waiting for logs. To exit press CTRL+C\n";

$callback = function (AMQPMessage $message) {
    $routingKey = $message->getRoutingKey();
    $body = $message->getBody();

    echo "[x] $routingKey: $body\n";
};

$channel->basic_consume($queue_name, "", false, true, false, false, $callback);

try {
    $channel->consume();
} catch (Throwable $exception) {
    echo $exception->getMessage();
}

$channel->close();
$connection->close();

/*
php 05/receive_logs_topic.php "#"
php 05/receive_logs_topic.php "kern.*"
php 05/receive_logs_topic.php "*.critical"
php 05/receive_logs_topic.php "kern.*" "*.critical"

php 05/emit_log_topic.php "kern.critical" "Erro critico no kernel."
php 05/emit_log_topic.php "kern.warning" "Warning no kernel."
php 05/emit_log_topic.php "info.static" "Aviso do sistema."
*/
