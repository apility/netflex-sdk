<?php

// Netflex Web - front end framework functions
// Developer document. Not for production

// Start session
if (session_status() == PHP_SESSION_NONE) {
  session_start();
  if (isset(NF::$config['session']['lifetime'])) {
    ini_set('session.cookie_lifetime', NF::$config['session']['lifetime']);
    ini_set('session.gc_maxlifetime', NF::$config['session']['lifetime']);
  }
}

$basepath = __DIR__ . '/functions';
$mode = strpos($_GET['_path'], '_/') === 0 ? 'editor' : 'live';

foreach (glob($basepath . '/common/*.php') as $filename) {
  require_once($filename);
}

foreach (glob($basepath . '/' . $mode . '/*.php') as $filename) {
  require_once($filename);
}

