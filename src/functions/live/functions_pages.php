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
  return 'https://' . get_setting('site_cdn_direct') . '/' . get_page_content($area, $column);
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
  return 'https://' . get_setting('site_cdn_direct') . '/media/' . $compression . '/' . $dimensions . '/' . get_page_content($area, $column);
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
  return get_page_content($area, $column);
}

/**
 * Get content
 *
 * @param string $area
 * @param string $column = 'html'
 * @return string
 */
function get_page_content($area, $column = 'html')
{
  if (isset(NF::$site->content[$area]) && isset(NF::$site->content[$area][$column])) {
    return NF::$site->content[$area][$column];
  }

  return null;
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
  return "<$tag class=\"$class\">" . get_page_content($area, $column) . "</$tag>";
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
 * @param array $resolutions = [320, 480, 768, 992, 1200]
 * @return string
 */
function get_page_content_image($area, $column, $dimensions, $compression, $class = null, $fill = '255,255,255,0', $picture_class = null, $resolutions = [320, 480, 768, 992, 1200])
{
  if ($compression == 'o') {
    $dimensions = '';
  }

  $image = get_page_content($area, $column);
  $alt = get_page_content($area, 'description');
  $title = get_page_content($area, 'title');

  if ($image) {
    $domain = get_setting('site_cdn_protocol') . '://' . get_setting('site_cdn_url');
    $fill = ($compression === 'fill' ? ('/' . $fill) : '');
    $url = '/media/' . $compression . '/' . $dimensions . $fill . '/' . $image;
    $src = $domain . $url;

    $sources = '';

    if ($resolutions) {
      var_dump($resolutions);
      foreach ($resolutions as $resolution) {
        $sources .= <<<HTML
          <source srcset="{$src}?src={$resolution}w" media="(max-width: {$resolution}px)">
HTML;
      }
    }

    return <<<HTML
    <picture class="{$picture_class}">
      {$sources}
      <source srcset="{$src}">
      <img class="{$class}" src="{$src}" alt="{$alt}" title="{$title}" />
    </picture>
HTML;
  }
}
