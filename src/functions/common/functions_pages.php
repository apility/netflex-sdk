<?php

/**
 * Get list of contens
 *
 * @param string $area
 * @param string $column = null
 * @return array
 */
function get_page_content_list($area, $column = null)
{
  global $site;

  $content = NF::$site->content[$area];

  if ($content) {
    if ($column) {
      if (isset($content[0])) {
        $items = [];
        foreach ($content as $item) {
          $items[] = $item[$column];
        }
      } else {
        $items[] = $content[$column];
      }
    } else {
      if (isset($content[0])) {
        $items = [];
        foreach ($content as $item) {
          $items[] = $item;
        }
      } else {
        $items[] = $content;
      }
    }

    return $items;
  }

  return null;
}

/**
 * Get content only for display, not editable
 *
 * @param int $relation_id
 * @param string $area
 * @param string $column
 * @return string
 */
function get_display_content($relation_id, $area, $column)
{
  $content = json_decode(NF::$capi->get('builder/pages/' . $relation_id . '/content')->getBody(), true);

  foreach ($content as $item) {
    if ($item['area'] === $area) {
      return $item[$column];
    }
  }

  return null;
}

/**
 * Get row of content based on id
 *
 * @param int $id
 * @return array
 */
function get_content_array($id)
{
  global $site;

  if (isset(NF::$site->content['id_' . $id])) {
    return NF::$site->content['id_' . $id];
  }

  return null;
}

/**
 * Get all content of a page
 *
 * @param int $page_id
 * @return array
 */
function get_full_content_array($page_id)
{
  $content = NF::$cache->fetch("page/$page_id");

  if (!$content) {
    $content = [];
    $contentItems = json_decode(NF::$capi->get('builder/pages/' . $page_id . '/content')->getBody(), true);

    foreach ($contentItems as $item) {
      if (isset($content[$item['area']])) {
        $existing = $content[$item['area']];
        $content[$item['area']] = null;
        $content[$item['area']] = [];
        $content[$item['area']][] = $existing;

        if (!isset($content[$item['area']][0])) {
          $existing = $content[$item['area']];
          $content[$item['area']] = null;
          $content[$item['area']] = [];
          $content[$item['area']][] = $existing;
        }

        $content[$item['area']][] = $item;
      } else {
        $content[$item['area']] = $item;
      }

      $content['id_' . $item['id']] = $item;
    }

    NF::$cache->save('page/' . $page_id, $content);
  }

  return $content;
}

/**
 * Get name of page
 *
 * @param int $id
 * @return string
 */
function get_page_name($id)
{
  return get_page_data($id, 'name');
}

/**
 * Get page data
 *
 * @param int $id
 * @param string $field = null
 * @return string
 */
function get_page_data($id, $field = null)
{
  if ($field) {
    return NF::$site->pages[$id][$field];
  }

  return NF::$site->pages[$id];
}

/**
 * Get all page data
 *
 * @param int $id
 * @return string
 */
function get_page($id)
{
  return get_page_data($id);
}
