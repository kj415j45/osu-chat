<?php

require('vendor/autoload.php');
use \Osu_API_v2\API;
use \League\CLImate\CLImate;

$cli = new CLImate();
$cli->style->addCommand('error', 'red');

$input = $cli->input('Which method you would like to login?');
$input->accept(['Token', 'Password'], true);
$method = $input->prompt();

Input:

switch(strtolower($method)){
    case 'Token':
        $input = $cli->input('Your token type:');
        $tokenType = $input->prompt();
        $input = $cli->password('Your token:');
        $token = $input->prompt();
        $input = $cli->password('Your refresh token:');
        $refreshToken = $input->prompt();
        try{
            $osu = new API(API::Token, $tokenType, $token, $refreshToken);
        }catch(Exception $e){$cli->error('Invaild token');goto Input;}
        
    case 'password':
        $input = $cli->input('Your username:');
        $username = $input->prompt();
        $input = $cli->password('Your password:');
        $password = $input->prompt();
        try{
            $osu = new API(API::Password, $username, $password);
        }catch(Exception $e){$cli->error('Invaild username or password');goto Input;}
    default:
}

CreateStream:

$input = $cli->input('Choose a local port:');
$port = $input->prompt();
$errorNo = 0;
$server = stream_socket_server('udp://0.0.0.0:'.$port, $errorNo, $errorMsg, STREAM_SERVER_BIND);
stream_set_blocking($server, 0);

JoinChannel:

$channelList = json_decode($osu->chat_channels(), true);
$cli->table($channelList);

SelectChannel:

$input = $cli->input('Which channel you want to join?');
$channelName = $input->prompt();
foreach($channelList as $channel){
    if($channel['name'] == $channelName){
        $channelID = $channel['channel_id'];
        break;
    }
}
if(!isset($channelID)){$cli->error('No such channel.');goto SelectChannel;}

$lastMessageID = null;

while(true){
    $messages = json_decode($osu->chat_messages([$channelID], $lastMessageID));
    
    foreach($messages as $message){
        $cli->yellow()->inline("{$message->sender->username}: ")->white($message->content);
    }

    $lastMessageID = end($messages)->message_id??$lastMessageID;

    $msg = stream_get_contents($server);

    $msg = trim($msg);
    
    if($msg !== null && $msg !== '' && $msg !== false){
        $osu->chat_messages_new('channel', $channelID, $msg);
    }
    
    sleep(1);
}

?>
