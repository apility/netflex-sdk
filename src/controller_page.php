<?php

// If site uses ServerSide Rendering, process the request URL
// and render it server side if supported. Otherwise continue as normal.

require_once(__DIR__ . '/model/autoloader.php');

// Netflex Web - page controller. The foundation on how urls are handeled in netflex. This is the controller that finds pages and creates url assets and other arrays.
// Developer document. Not for production

// Get and clean url for security
$real_url = explode('?', urldecode($_GET['_path']))[0];
$url = trim($real_url, '/');

if (strpos($url, '_/') === 0) {
  global $url_asset;
  $url_part = explode('/', $url);
  $url_asset[1] = array_pop($url_part);
  $token = $url_asset[1];

  if (NF::$jwt->verify($token)) {
    global $payload;
    $payload = NF::$jwt->decode($token);
    $controller = NF::nfPath('pagefinder/' . $payload->scope . '_' . $payload->relation . '.php');

    if (file_exists($controller)) {
      require $controller;
      die();
    }
  }

  http_response_code(401);
  die('Invalid or expired token');
}

if ($url == '') {
  $url = 'index';
}

$url = $url . '/';

// Get full url for checking redirects
$fullUrl = ltrim($_SERVER['REQUEST_URI'], '/');

// Log url and full url
NF::debug($url, 'Path');
NF::debug($fullUrl, 'Full URI');

// Cache class
if ($url == 'CacheStore/') {
  if (isset($_GET['key'])) {
    if (NF::$cache->delete($_GET['key'])) {
      echo 'Key deleted';
    } else {
      echo 'Key does not exist';
    }
  } else {
    echo 'Key is missing';
  }
  exit;
}

// Prepare url
$url_part = explode('/', $url);
$url_levels = count($url_part) - 1;
unset($url_part[$url_levels]);

// Check for redirects
$url_redirect = get_redirect($url, 'target_url');

if ($url_redirect !== 0) {
  header('Location: ' . $url_redirect . '', true, get_redirect($url, 'type'));
  exit;
}

// Check for full url redirects
$url_redirect = get_redirect($fullUrl, 'target_url');

if ($url_redirect !== 0) {
  header('Location: ' . $url_redirect . '', true, get_redirect($fullUrl, 'type'));
  exit;
}

// Regular redirects
switch ($url) {
  case 'sitemap.xml/':
    require NF::nfPath('seo/sitemap.xml.php');
    exit;
  case 'sitemap.xsl/':
    require NF::nfPath('seo/sitemap.xsl.php');
    exit;
  case 'robots.txt/':
    require NF::nfPath('seo/robots.php');
    exit;
  default:
    break;
}

// Find page
$found_page = 0;
$process_url = $url;
$found_url_level = $url_levels;
$new_url_part = $url_part;

if (isset(NF::$config['domains']['default'])) {
  require NF::nfPath('pagefinder/domainrouting.php');
} else {
  require NF::nfPath('pagefinder/default.php');
}
