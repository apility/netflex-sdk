<?php

global $page;
global $page_id;
global $revision;
global $editmode;
global $edit_tools;
global $_mode;
global $_domain;
global $url_asset;
global $entry_override;
global $revision_override;
global $previewmode;

$_mode = $payload->mode;
$_domain = $payload->domain;
$entry_id = $payload->entry_id;
$entry_revision = $payload->revision_id;
$entry = get_directory_entry($entry_id);
$editmode = 0;
$previewmode = 1;

NF::$site->loadGlobals();
$structure = json_decode(NF::$capi->get('builder/structures/' . $entry['directory_id'])->getBody(), true);

NF::$site->loadPage($structure['canonical_page_id'], NULL);


$entry = json_decode(NF::$capi->get('builder/structures/entry/' . $entry_id . '/revision/' . $entry_revision)->getBody(), true);

$page_id = $structure['canonical_page_id'];

$url_asset = [];
$url_asset[0] = '/';

switch (trim($structure['url_scheme'], '/')) {
  case 'url':
    $url_asset[1] = rtrim($entry['url'], '/');
    break;
  case 'id':
    $url_asset[1] = $entry['id'];
    break;
  case 'id/url':
    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = $entry['id'];
    break;
  case 'yyyy/mm/dd/url':
    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = convert_datetime($entry['created'], 'd');
    $url_asset[3] = convert_datetime($entry['created'], 'm');
    $url_asset[4] = convert_datetime($entry['created'], 'Y');
    break;
  case 'yyyy/mm/url':
    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = convert_datetime($entry['created'], 'm');
    $url_asset[3] = convert_datetime($entry['created'], 'Y');
    break;
  case 'yyyy/url':
    $url_asset[1] = rtrim($entry['url'], '/');
    $url_asset[2] = convert_datetime($entry['created'], 'Y');
    break;
  default:
    die('Invalid url_scheme');
}

$entry_override = $payload->entry_id;
$revision_override = $entry_revision;

NF::$site->loadGlobals();
$page = get_page($page_id);
$revision = $page['revision'];

$edit_tools = '<script src="/assets/js/holder/holder.js"></script>';
$edit_tools .= '<script>$(function(){ $("a").click(function(e) { e.preventDefault();  }); });</script>';

require NF::nfPath('build_template.php');
