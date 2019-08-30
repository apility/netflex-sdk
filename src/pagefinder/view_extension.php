<?php
global $_domain;

$_domain = $payload->domain;

if (isset($payload->get)) {
  foreach ($payload->get as $key => $value) {
    if (!isset($_GET[$key])) {
      $_GET[$key] = $value;
    }
  }
}

$extensions = json_decode(get_setting('netflexapp_extensions'), true);
if ($extensions && is_array($extensions)) {
  $extensions = array_filter($extensions, function ($extension) use ($payload) {
    return $extension['alias'] === $payload->view;
  });

  if ($extension = array_shift($extensions)) {
    if (file_exists($extension['file'])) {
      NF::$site->loadGlobals();
      require_once($extension['file']);
      die();
    }
  }
}

NF::debug('Extension "' . $payload->view . '" not found (' . $payload->file . ')');
