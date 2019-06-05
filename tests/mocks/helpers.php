<?php

$contentId = 10000;

/**
 * Inserts content if it doesnt exists
 * returns the content
 *
 * @param string $area
 * @param array $content
 * @return array
 */
function insertContentIfNotExists($area, $content)
{
  global $contentId;

  if (!array_key_exists($area, NF::$site->content)) {
    $content = [
      'id' => $contentId++,
      $area => $content
    ];

    NF::$site->content[$area] = $content;

    return $content;
  }
}
