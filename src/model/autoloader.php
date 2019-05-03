<?php

require_once(__DIR__ . '/helpers/array_find.php');
require_once(__DIR__ . '/helpers/object_map.php');
require_once(__DIR__ . '/FieldMapping.php');
require_once(__DIR__ . '/StructureQueryPage.php');
require_once(__DIR__ . '/StructureQuery.php');
require_once(__DIR__ . '/Structure.php');

spl_autoload_register('modelsAutoloader');

function modelsAutoloader($className)
{
  $classPath = explode('\\', $className);
  if (count($classPath) && strtolower($classPath[0]) === 'models') {
    array_shift($classPath);
    $classPath = 'models' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $classPath) . '.php';

    if (file_exists($classPath)) {
      include($classPath);
    }
  }
}
