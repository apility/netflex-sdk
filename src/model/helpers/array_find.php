<?php

function array_find($array, $callback)
{
  $r = new ReflectionFunction($callback);
  $argLength = $r->getNumberOfRequiredParameters();

  foreach ($array as $key => $item) {
    $a = $argLength === 1 ? $item : $key;
    $b = $argLength > 1 ? $item : $key;
    if ($callback($a, $b)) {
      return $item;
    }
  }

  return null;
}
