<?php

/**
 * Get file with automatic cdn url
 *
 * @param string $area
 * @param string $column
 * @return string
 */
function get_page_content_file($area, $column)
{
  global $page_id;
  global $revision;

  $content = insertContentIfNotExists($area, [
    'relation' => 'page',
    'relation_id' => $page_id,
    'revision' => $revision,
    'area' => $area,
    'type' => $column
  ]);

  return <<<HTML
https://{ get_setting('site_cdn_direct') }/{ $content[$column] }
HTML;
}

/**
 * Get compressed image from cdn
 *
 * @param string $area
 * @param string $column
 * @param string $dimensions
 * @param string $compression
 * @return string
 */
function get_page_content_mediafile($area, $column, $dimensions, $compression)
{
  global $page_id;
  global $revision;

  $content = insertContentIfNotExists($area, [
    'relation' => 'page',
    'relation_id' => $page_id,
    'revision' => $revision,
    'area' => $area,
    'type' => $column
  ]);

  return 'https://' . get_setting('site_cdn_direct') . '/media/' . $compression . '/' . $dimensions . '/' . $content[$column];
}

/**
 * Get clean content from db, with no editor
 *
 * @param string $area
 * @param string $column
 * @return string
 */
function get_page_content_string($area, $column)
{
  global $page_id;
  global $revision;

  $content = insertContentIfNotExists($area, [
    'relation' => 'page',
    'relation_id' => $page_id,
    'revision' => $revision,
    'area' => $area,
    'type' => $column
  ]);

  return $content[$column];
}

/**
 * Get content
 *
 * @param string $area
 * @param string $column = 'html'
 * @param string $tag = 'div'
 * @param string $class = null
 * @return string
 */
function get_page_content($area, $column = 'html', $tag = 'div', $class = null)
{
  global $page_id;
  global $revision;
  global $_mode;

  $content = insertContentIfNotExists($area, [
    'relation' => 'page',
    'relation_id' => $page_id,
    'revision' => $revision,
    'area' => $area,
    'type' => $column
  ]);

  if ($_mode === 'preview') {
    return $content[$column];
  }

  return <<<HTML
    <$tag
      id="e-{$content['id']}-$column"
      class="$class"
      data-content-area="$area"
      data-content-type="$column"
      data-content-id="{$content['id']}"
      contenteditable="true">
      {$content[$column]}
    </$tag>
HTML;
}

/**
 * Get content with wrapper
 *
 * @param string $area
 * @param string $column
 * @param string $tag
 * @param string $class
 * @return string
 */
function get_page_content_wrap($area, $column = 'html', $tag = 'div', $class = null)
{
  global $_mode;

  if ($_mode === 'preview') {
    return "<$tag class=\"$class\">" . get_page_content($area, $column) . "</$tag>";
  }

  return get_page_content($area, $column, $tag, $class);
}

/**
 * Get autocompressed image from netflexsrc with class, type and dimensions. Dimensions are set for height and width
 *
 * @param string $area
 * @param string $column
 * @param string $dimensions
 * @param string $compression
 * @param string $class = null
 * @param string $fill = '255,255,255'
 * @return string
 */
function get_page_content_image($area, $column, $dimensions, $compression, $class = null, $fill = '255,255,255,0', $picture_class = null, $resolutions = [])
{
  global $page_id;
  global $revision;

  $image = insertContentIfNotExists($area, [
    'relation' => 'page',
    'relation_id' => $page_id,
    'revision' => $revision,
    'area' => $area,
    'type' => $column
  ]);

  $alt = insertContentIfNotExists($area, [
    'relation' => 'page',
    'relation_id' => $page_id,
    'revision' => $revision,
    'area' => $area,
    'type' => 'alt'
  ])['alt'];

  $title = insertContentIfNotExists($area, [
    'relation' => 'page',
    'relation_id' => $page_id,
    'revision' => $revision,
    'area' => $area,
    'type' => 'title'
  ])['title'];

  $dimensions = $compression === 'o' ? '' : $dimensions;

  $src = 'https://placehold.it/' . $dimensions;

  if ($image[$column]) {
    $domain = get_setting('site_cdn_protocol') . '://' . get_setting('site_cdn_url');
    $fill = ($compression === 'fill' ? ('/' . $fill) : '');
    $url = '/media/' . $compression . '/' . $dimensions . $fill . '/' . $image[$column];
    $src = $domain . $url;
  }

  return <<<HTML
    <picture
      id="e-{$image['id']}-$column"
      class="$picture_class $class find-image"
      data-content-area="$area"
      data-content-type="$column"
      data-content-dimensions="$dimensions"
      data-content-compressiontype="$compression"
      data-content-id="{$image['id']}"
    >
		  <source srcset="$src?src=320w" media="(max-width: 320px)">
		  <source srcset="$src?src=480w" media="(max-width: 480px)">
		  <source srcset="$src?src=768w" media="(max-width: 768px)">
		  <source srcset="$src?src=992w" media="(max-width: 992px)">
		  <source srcset="$src?src=1200w" media="(max-width: 1200px)">
		  <source srcset="$src">
		  <img class="$class" src="$src" alt="$alt" title="$title" />
		</picture>
HTML;
}
