<?php

# Should log to the same directory as this file
require dirname(__FILE__) . '/../src/CIOLogger.php';

$log   = CIOLogger::instance(dirname(__FILE__), CIOLogger::DEBUG, 'Test');
$args1 = array('a' => array('b' => 'c'), 'd');
$args2 = NULL;

if(($id == $log->logInfo('Info Test'))){
    echo "Info Log ID: $id\n";
}
if(($id == $log->logNotice('Notice Test'))){
    echo "Notice Log ID: $id\n";
}
if(($id == $log->logWarn('Warn Test'))){
    echo "Warn Log ID: $id\n";
}
if(($id == $log->logError('Error Test'))){
    echo "Error Log ID: $id\n";
}
if(($id == $log->logFatal('Fatal Test'))){
    echo "Fatal Log ID: $id\n";
}
if(($id == $log->logAlert('Alert Test'))){
    echo "Alert Log ID: $id\n";
}
if(($id == $log->logCrit('Crit test'))){
    echo "Crit Log ID: $id\n";
}
if(($id == $log->logEmerg('Emerg Test'))){
    echo "Emerg Log ID: $id\n";
}

$log->logInfo('Testing passing an array or object', $args1);
$log->logWarn('Testing passing a NULL value', $args2);
