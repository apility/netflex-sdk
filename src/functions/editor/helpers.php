<?php

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
  if (!NF::$site->content[$area]) {
    $response = json_decode(NF::$capi->post('builder/content', ['json' => $content])->getBody());
    NF::$site->content[$area] = json_decode(NF::$capi->get('builder/content/' . $response->content_id)->getBody(), true);
  }

  return NF::$site->content[$area];
}
