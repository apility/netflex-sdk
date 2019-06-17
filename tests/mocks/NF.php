<?php

require_once(__DIR__ . '/Cache.php');
require_once(__DIR__ . '/Guzzle.php');
require_once(__DIR__ . '/Site.php');

NF::$capi = new MockGuzzle();
NF::$cache = new MockCache();
NF::$site = new MockSite();
NF::$site_root = __DIR__ . '/project/';
