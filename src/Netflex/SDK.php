<?php

namespace Netflex;

use NF;

class SDK {
  /** @var string Path to the legacy bootstrap script */
  const bootstrap = __DIR__ . '/../../bootstrap.php';

  public static function init () {
    global $current_date;
    global $edit_tools;
    global $url_asset;
    global $tested_url;

    $_GET['_path'] = ltrim($_SERVER['REQUEST_URI'], '/');
    NF::init(getenv('SERVER_SITENAME'));

    // Set standard variables
    $current_date = date('Y-m-d H:i:s');
    $edit_tools = null;
    $url_asset = null;
    $tested_url = null;

    // Load globals
    NF::$site->loadGlobals();

    // Start page generation
    require NF::nfPath('functions.php');
    require NF::nfPath('controller_page.php');

    if ($page_id) {
      NF::$site = new \Netflex\Site\Site($page_id, $revision);
      $site = NF::$site;
      require NF::nfPath('build_template.php');
      die();
    }

    require NF::nfPath('build_error.php');
  }
}
