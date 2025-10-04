<?php

require_once("../vendor/autoload.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection("rabbitmq", 5672, "guest", "guest");

$channel = $connection->channel();

// Cria o Exchange "p_main" do tipo "fanout"
$channel->exchange_declare("exg.p_main", "fanout", false, true, false);

// Cria a Fila "p_01"
$channel->queue_declare("p_01", false, true, false, false);
$channel->queue_bind("p_01", "exg.p_main");

// Cria a Fila "p_02"
$channel->queue_declare("p_02", false, true, false, false);
$channel->queue_bind("p_02", "exg.p_main");

$channel->close();
$connection->close();
