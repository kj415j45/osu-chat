<?php

require('vendor/autoload.php');
use \League\CLImate\CLImate;

$cli = new CLImate();
$cli->style->addCommand('error', 'red');

CreateSocket:

$input = $cli->input('osu-chat local port:');
$port = $input->prompt();

$client = stream_socket_client('udp://127.0.0.1:'.$port);

while(true){
    $msg = fgets(fopen('php://stdin', 'r'));
    fwrite($client, $msg, strlen($msg));
}

?>
