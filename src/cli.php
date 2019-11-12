<?php

$_GET['_path'] = '';

NF::setRoot(realpath(__DIR__ . '/../../../../') . '/');
NF::init();
NF::$site->loadGlobals();

// Load global helper methods
require_once(NF::nfPath('model/autoloader.php'));
require_once(NF::nfPath('functions.php'));
