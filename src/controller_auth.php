<?php

// Get url to login
if (isset(NF::$config['domains']['default']) && isset(NF::$config['auth'][$routing_domain]['login_url'])) {
  $loginurl = NF::$config['auth'][$routing_domain]['login_url'];
} else {
  $loginurl = NF::$config['auth']['login_url'];
}
if ($loginurl == null) {
  $loginurl = '/login/';
}

// Check if user is logged in
if (isset($_SESSION['netflex_siteuser'])) {
  //Check if current user has access to this page

  if (check_access($_SESSION['netflex_siteuser'], $page['authgroups'])) { } else {
    $_SESSION['netflex_error'] = 'NO_ACCESS';
    header('Location: ' . $loginurl);
    exit;
  }
} else {
  header('Location: ' . $loginurl);
  exit;
}
