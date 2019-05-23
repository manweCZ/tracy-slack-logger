<?php
require_once __DIR__.'/../vendor/autoload.php';

$logger = new \BiteIT\TracySlackLogger('https://hooks.slack.com/services/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');
//$logger->setEnabledMessageData([\BiteIT\TracySlackLogger::MESSAGE_USER_AGENT]);
$logger->setDisabledMessageData(\BiteIT\TracySlackLogger::MESSAGE_IP);

\Tracy\Debugger::enable([$_SERVER['REMOTE_ADDR']]);
\Tracy\Debugger::$productionMode = true;
\Tracy\Debugger::setLogger( $logger );

$error();