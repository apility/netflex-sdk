<?php

namespace Netflex\Site;

use NF;

class Store
{

  public function __construct()
  {
    if (!is_dir(NF::$site_root . '/store')) {
      mkdir(NF::$site_root . '/store');
    }
  }

  public function get($file)
  {

    $datastore = NF::$site_root . '/store/';
    $filepath = $datastore . $file;
    if (file_exists($filepath)) {
      return file_get_contents($filepath);
    } else {
      NF::debug('Store ' . $file . ' does not exist', 'Get store ' . $file);
      return null;
    }
  }

  public function getPath($file)
  {

    $datastore = NF::$site_root . '/store/';
    return $datastore . $file;
  }

  public function update($data, $file)
  {

    $datastore = NF::$site_root . '/store/';
    $filepath = $datastore . $file;

    if (file_exists($filepath)) {
      file_put_contents($filepath, $data);
      return true;
    } else {
      NF::debug('Creating store ' . $file, 'Update  store ' . $file);
      file_put_contents($filepath, $data);
      return true;
    }
  }

  public function destroy($file)
  {

    $datastore = NF::$site_root . '/store/';
    $filepath = $datastore . $file;

    if (file_exists($filepath)) {
      unlink($filepath);
      return true;
    } else {
      return true;
    }
  }
}
