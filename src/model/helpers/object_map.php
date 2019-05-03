<?php

function object_map($object, $callback)
{
  $mapped = [];

  foreach ($object as $key => $value) {
    $mapped[$key] = $callback($key, $value);
  }

  return (object)$mapped;
}
