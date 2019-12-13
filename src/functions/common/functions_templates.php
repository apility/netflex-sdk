<?php

/**
 * Get template part
 *
 * @param string $alias
 * @return void
 */
function get_template_part($alias)
{
  global $page;
  global $templatealias;
  global $url_asset;

  NF::debug('templates/' . $templatealias . '/' . $alias, 'part');
  require(NF::$site_root . '/templates/' . $templatealias . '/' . $alias . '.php');
  NF::debug('templates/' . $templatealias . '/' . $alias, '!part');
}



if(!function_exists('get_block')) {
  /**
   * Get block template
   *
   * @param string $alias
   * @param array $vars = []
   * @return void
   */
  function get_block($alias, $vars = [])
  {
    global $page;
    global $url_asset;

    if (is_array($vars)) {
      extract($vars, EXTR_SKIP);
    }

    NF::debug('blocks/' . $alias, 'block');
    require(NF::$site_root . 'blocks/' . $alias . '.php');
    NF::debug('blocks/' . $alias, '!block');
  }
}
/**
 * Get content inside block
 *
 * @param string $block
 * @param string $area
 * @param string $column
 * @return mixed
 */
function get_static_content($block, $area, $column)
{
  global $site;
  global $current_date;

  if (
    isset(NF::$site->statics[$block][$area]) &&
    isset(NF::$site->statics[$block][$area][$column])
  ) {
    return NF::$site->statics[$block][$area][$column];
  }
}

/**
 * Get meta title
 *
 * @return string
 */
function get_meta_title()
{
  global $page;
  global $addto_meta_title_start;
  global $addto_meta_title_end;
  global $force_meta_title;

  $page_id = $page['id'];
  $global_meta = get_setting('site_meta_title');

  if ($page['title'] == null) {
    $metatitle = $page['name'] . '' . $global_meta;
  } else {
    $metatitle = $page['title'];
  }

  if ($force_meta_title != null) {
    return $force_meta_title;
  }

  return $addto_meta_title_start . '' . $metatitle . '' . $addto_meta_title_end;
}

/**
 * Get meta description
 *
 * @return string
 */
function get_meta_description()
{
  global $page;
  global $force_meta_description;

  $global_desc = get_setting('site_meta_description');

  if ($page['description'] == null) {
    $metadesc = $global_desc;
  } else {
    $metadesc = $page['description'];
  }

  if ($force_meta_description != null) {
    return $force_meta_description;
  }

  return $metadesc;
}

/**
 * Get meta keywords
 *
 * @return string
 */
function get_meta_keywords()
{
  global $page;
  global $force_meta_keywords;

  $global_key = get_setting('site_meta_keywords');

  if ($page['keywords'] == null) {
    $metakey = $global_key;
  } else {
    $metakey = $page['keywords'];
  }

  if ($force_meta_keywords != null) {
    return $force_meta_keywords;
  }

  return $metakey;
}

/**
 * Get meta author
 *
 * @return string
 */
function get_meta_author()
{
  global $page;
  global $force_meta_author;

  $global_author = get_setting('site_meta_author');

  if ($page['author'] == null) {
    $metaauthor = $global_author;
  } else {
    $metaauthor = $page['author'];
  }

  if ($force_meta_author != null) {
    return $force_meta_author;
  }

  return $metaauthor;
}

/**
 * Get body class
 *
 * @return string
 */
function get_body_class()
{
  global $page;

  if ($page['body_class']) {
    return 'class="' . $page['body_class'] . '"';
  }
}

/**
 * Get code to inject into head
 *
 * @return string
 */
function get_codeinject_head()
{
  global $page;

  if ($page['add_to_head']) {
    return $page['add_to_head'];
  }
}

/**
 * Get code to inject before body close
 *
 * @return string
 */
function get_codeinject_bodyclose()
{
  global $page;

  if ($page['add_to_bodyclose']) {
    return $page['add_to_bodyclose'];
  }
}

/**
 * Get an asset url
 *
 * @param string $asset
 * @return string
 */
function get_asset($asset)
{
  global $_domain;
  $hash = null;

  if (isset(NF::$config['deploy']) && isset(NF::$config['deploy']['id'])) {
    $hash = NF::$config['deploy']['id'];
  }

  $hash = $hash ?? time();

  return ($_domain ? $_domain : '') . '/assets/' . $asset . '?' . $hash;
}
