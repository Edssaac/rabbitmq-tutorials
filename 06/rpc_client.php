<?php

require_once("../vendor/autoload.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FibonacciRpcClient
{
    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

        $this->channel = $this->connection->channel();

        list($this->callback_queue,,) = $this->channel->queue_declare('', false, false, true, false);

        $this->channel->basic_consume(
            $this->callback_queue,
            '',
            false,
            true,
            false,
            false,
            [
                $this,
                'onResponse'
            ]
        );
    }

    public function onResponse($rep)
    {
        if ($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    public function call($number)
    {
        $this->response = null;
        $this->corr_id = uniqid();

        $message = new AMQPMessage(
            (string) $number,
            [
                'correlation_id' => $this->corr_id,
                'reply_to' => $this->callback_queue
            ]
        );

        $this->channel->basic_publish($message, '', 'rpc_queue');

        while (!$this->response) {
            $this->channel->wait();
        }

        return intval($this->response);
    }
}

$fibonacci_rpc = new FibonacciRpcClient();

$response = $fibonacci_rpc->call(31);

echo ' [.] Got ', $response, "\n";

/*
php 06/rpc_server.php
php 06/rpc_client.php
*/
