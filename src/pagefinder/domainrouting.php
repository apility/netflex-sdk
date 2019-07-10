<?php

// Configure routing
if (isset($_SESSION['dev_domain']) && getenv('ENV') !== 'master') {
  $routing_domain = $_SESSION['dev_domain'];
} else if (isset(NF::$config['domains']['dev_domain']) && getenv('ENV') !== 'master') {
  $routing_domain = NF::$config['domains']['dev_domain'];
} else {
  $routing_domain = explode(':', $_SERVER['HTTP_HOST'])[0];
}

NF::debug('Domain', $routing_domain);

// Define routes
$routes = NF::$config['routes'];

// Get group
if (isset(NF::$config['domains'][$routing_domain])) {
  $group = NF::$config['domains'][$routing_domain];
} else {
  $group = NF::$config['domains']['default'];
}

// Set standards
$foundit = 0;
$found_page_level = null;
$page = null;
$page_id = null;

// Check full url first
while ($found_url_level > 0 && $found_page_level == null) {

  NF::debug('Process url', $process_url);

  // Check if url has a route
  if (isset($routes[$process_url]) || isset($routes[$routing_domain][$process_url])) {

    // Debug page data
    $netflex_debug = [];
    $netflex_debug['Current URL'] = $url;
    $netflex_debug['Number of URL Levels'] = $url_levels;
    $netflex_debug['All URL Parts'] = $url_part;
    $netflex_debug['The tested URL'] = $tested_url;
    $netflex_debug['URL Assets'] = $url_asset;
    $netflex_debug['URL Level the route was found on'] = $found_page_level;
    NF::debug($netflex_debug, 'URL Parser Data');

    // Load route
    if (isset($routes[$routing_domain][$process_url])) {
      NF::debug($process_url . ' => ' . $routes[$routing_domain][$process_url], 'Route');
      require(NF::$site_root . '/' . $routes[$routing_domain][$process_url]);
    } else {
      NF::debug($routing_domain, 'Routing domain');
      require(NF::$site_root . '/' . $routes[$process_url]);
    }

    die();
  }

  $pages = NF::$site->pages;

  foreach ($pages as $paged) {
    if (
      $paged['url'] == $process_url &&
      $paged['published'] &&
      $paged['group_id'] == $group &&
      $paged['template'] != 'f' &&
      $paged['template'] != 'i' &&
      $paged['template'] != 'e'
    ) {
      $foundit = 1;
      $found_id = $paged['id'];
      break;
    }
  }

  if ($foundit) {
    // Set the found url to the current process url
    $page = $pages[$found_id];
    $found_url = $process_url;
    $found_page_level = $found_url_level;

    // Unset all strings used to process
    unset($process_url);
    unset($newurl);
    unset($new_url_part);
  } else {
    $i = 0;
    $newurl = '';
    // Store tested url
    $tested_url[$found_url_level] = $process_url;
    // Increase level by 1
    $found_url_level = $found_url_level - 1;
    // Create asset string for this level
    $url_asset[$url_levels - $found_url_level] = $new_url_part[$found_url_level];

    // Unset end string of array
    unset($new_url_part[$found_url_level]);

    // Create new url
    foreach ($new_url_part as $parturl) {
      $i++;
      $newurl .= $parturl;
      if ($i != $found_url_level) {
        $newurl .= '/';
      }
    }

    //Set new url to process
    $process_url = $newurl . '/';
  }
}

if ($found_page_level != null) {
  //Indicate that page is found
  $found_page = 1;
  $master = null;

  // Set page revision
  $revision = $page['revision'];
  $page_id = $page['id'];
  global $page;

  // Debug page data
  NF::debug($page, 'Page data');
  $netflex_debug = [];
  $netflex_debug['Current URL'] = $url;
  $netflex_debug['Number of URL Levels'] = $url_levels;
  $netflex_debug['All URL Parts'] = $url_part;
  $netflex_debug['Is this a redirect?'] = $url_redirect;
  $netflex_debug['The tested URL'] = $tested_url;
  $netflex_debug['URL Assets'] = $url_asset;
  $netflex_debug['Found page'] = $found_page;
  $netflex_debug['Domain'] = $routing_domain;
  $netflex_debug['Group'] = $group;
  $netflex_debug['Group name'] = $pages[$group]['name'];
  $netflex_debug['URL Level the page was found on'] = $found_page_level;
  NF::debug($netflex_debug, 'URL Parser Data');

  // Check if page is public
  if (!$page['public']) {
    require NF::nfPath('controller_auth.php');
  }
}
