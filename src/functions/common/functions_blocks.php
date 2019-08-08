<?php

/** @global string $blockhash Unique hash for the current block */
global $blockhash;

/** @var array */
$edit_areas = [];

/**
 * Get page blocks
 *
 * @param string $area
 * @param array $vars = null
 * @return void
 */
function get_page_blocks($area, $vars = null)
{
  global $blockhash;
  global $site;
  global $page;
  global $url_asset;

  NF::debug($area, 'area');
  $content = isset(NF::$site->content[$area]) ? NF::$site->content[$area] : null;

  if (is_array($vars) === true) {
    extract($vars, EXTR_SKIP);
  }

  if (empty($content) == false) {
    if (isset($content[0]) == false) {
      $content = [$content];
    }

    foreach ($content as $section) {
      $block = $section['text'];
      $alias = NF::$site->templates['components'][$block]['alias'];
      $blockhash = $section['title'];
      NF::debug('components/' . $alias, 'component');
      require(NF::$site_root . 'components/' . $alias . '.php');
      NF::debug('components/' . $alias, '!component');
    }
  }

  NF::debug($area, '!area');
}

/**
 * Display page blocks
 *
 * @param int $page_id
 * @param string $area
 * @param array $vars = null
 * @return void
 */
function display_page_blocks($page_id, $area, $vars = null)
{
  global $blockhash;
  global $site;
  global $page;
  global $url_asset;

  NF::debug($area, 'copy_area');


  $content = get_full_content_array($page_id);
  $content = isset($content[$area]) ? $content[$area] : null;

  if (is_array($vars) === true) {
    extract($vars, EXTR_SKIP);
  }

  if (empty($content) == false) {

    if (isset($content[0]) == false) {
      $content = [$content];
    }

    $current_page_content = NF::$site->content;
    NF::$site->content = get_full_content_array($page_id);

    foreach ($content as $section) {
      $block = $section['text'];
      $alias = NF::$site->templates['components'][$block]['alias'];
      $blockhash = $section['title'];
      NF::debug('components/' . $alias, 'component');

      require(NF::$site_root . 'components/' . $alias . '.php');
      NF::debug('components/' . $alias, '!component');
    }

    NF::$site->content = $current_page_content;
  }
  NF::debug($area, '!copy_area');
}

/**
 * Get number of blocks in area
 *
 * @param string $area
 * @return int
 */
function get_page_blocks_count($area)
{
  global $site;
  $blocks = NF::$site->content[$area] ?? [];
  return count($blocks);
}

/**
 * Get block content
 *
 * @param string $area
 * @param string $tag = null
 * @param string $class = null
 * @return mixed
 */
function get_block_content($area, $tag = null, $class = null)
{
  global $blockhash;

  $area = $area . '_' . $blockhash;

  if ($tag) {
    return get_page_content_wrap($area, 'html', $tag, $class);
  }

  return get_page_content($area);
}

/**
 * Get block content wrap
 *
 * @param string $area
 * @param string $tag
 * @param string $class = null
 * @return mixed
 */
function get_block_content_wrap($area, $tag, $class = null)
{
  return get_block_content($area, $tag, $class);
}

/**
 * Get image with class and dimensions
 *
 * @param string $area
 * @param string $dimensions
 * @param string $compression_type
 * @param string $class = null
 * @param string $fill = null
 * @param string $picture_class = null
 * @param array $resolutions = []
 * @return string
 */
function get_block_content_image($area, $dimensions, $compression_type, $class = null, $fill = null, $picture_class = null, $resolutions = [])
{
  global $blockhash;

  if ($compression_type === 'fill') {
    $fill = $fill ?? '255,255,255';
  }

  $area = $area . '_' . $blockhash;
  $image = get_page_content_string($area, 'image');
  $alt = get_page_content_string($area, 'description');
  $title = get_page_content_string($area, 'title');

  return get_page_content_image($area, 'image', $dimensions, $compression_type, $class, $fill, $picture_class, $resolutions);
}

/**
 * Get block content list
 *
 * @param array $settings
 * @return array|null
 */
function get_block_content_list($settings)
{
  global $blockhash;
  global $site;

  $returnfield = $settings['content_field'];
  $area = $settings['alias'] . '_' . $blockhash;
  if (isset(NF::$site->content[$area])) {
    $content = NF::$site->content[$area];
  } else {
    $content = [];
  }

  if (empty($content) == false) {

    $items = [];

    if (isset($content[0]) == false) {
      $items[] = $content[$returnfield];
    } else {
      foreach ($content as $item) {
        $items[] = $item[$returnfield];
      }
    }

    return $items;
  }
}

/**
 * Get block content string
 *
 * @param array $settings
 * @return string
 */
function get_block_content_string($settings)
{
  global $blockhash;

  $area = $settings['alias'] . '_' . $blockhash;
  $settings['alias'] = $area;
  $returnfield = $settings['content_field'];

  NF::debug($area, 'get_block_content_string');
  NF::debug($returnfield, 'get_block_content_string');
  NF::debug(get_page_content_string($area, $returnfield), 'get_block_content_string');

  return get_page_content_string($area, $returnfield);
}
