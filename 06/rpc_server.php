<?php

require_once("../vendor/autoload.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');

$channel = $connection->channel();

$channel->queue_declare('rpc_queue', false, false, false, false);

function fib($number)
{
    if ($number == 0) {
        return 0;
    }

    if ($number == 1) {
        return 1;
    }

    return fib($number - 1) + fib($number - 2);
}

echo " [x] Awaiting RPC requests\n";

$callback = function ($req) {
    $number = intval($req->getBody());

    echo ' [.] fib(', $number, ")\n";

    $message = new AMQPMessage(
        (string) fib($number),
        [
            'correlation_id' => $req->get('correlation_id')
        ]
    );

    $req->getChannel()->basic_publish($message, '', $req->get('reply_to'));

    $req->ack();
};

$channel->basic_qos(null, 1, false);

$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

try {
    $channel->consume();
} catch (Throwable $exception) {
    echo $exception->getMessage();
}

$channel->close();
$connection->close();
