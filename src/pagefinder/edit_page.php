<?php

global $page;
global $page_id;
global $revision;
global $editmode;
global $previewmode;
global $edit_tools;
global $_mode;
global $_domain;
global $url_asset;

$_mode = $payload->mode;
$_domain = $payload->domain;
$page_id = $payload->page_id;

NF::$site->loadGlobals();

$page = get_page($page_id);
$revision = $payload->revision_id ?? null;

NF::$site = new \Netflex\Site\Site($page_id, $revision);

foreach ($payload->session as $key => $value) {
  $_SESSION[$key] = $value;
}

$site = NF::$site;

$edit_tools = '<script src="/assets/js/holder/holder.js"></script>';
$edit_tools .= '<script>$(function(){ $("a").click(function(e) { e.preventDefault();  }); });</script>';

$editmode = 0;
$previewmode = 1;

if ($payload->mode !== 'preview') {
  $edit_tools = $payload->edit_tools;
  $editmode = 1;
  $previewmode = 0;
}

$url_asset = null;

require NF::nfPath('build_template.php');

die();
