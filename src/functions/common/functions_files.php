<?php

/**
 * Get CDN URL for file
 *
 * @param string|array $file
 * @return string
 */
function get_cdn_url($file)
{
  if (is_array($file)) {
    $file = $file['path'];
  }

  return get_setting('site_cdn_protocol') . '://' . get_setting('site_cdn_url') . '/' . $file;
}

/**
 * Get file data
 *
 * @param int $file_id
 * @param array $data
 * @return mixed
 */
function get_file_data($file_id, $data)
{
  $file = json_decode(NF::$capi->get('files/file/' . $file_id)->getBody(), true);

  if ($file['id']) {
    if ($data == 'url') {
      return $file['path'];
    }

    return $file[$data];
  }

  return 0;
}

/**
 * Get CDN media
 *
 * @param string|array $file
 * @param string $dimensions (e.g, '100x100')
 * @param string $compressiontype (e.g 'rc')
 * @param string $color = '0,0,0'
 * @return string
 */
function get_cdn_media($file, $dimensions, $compressiontype, $color = '0,0,0')
{
  if (is_array($file)) {
    $file = $file['path'];
  }

  if ($compressiontype == 'fill') {
    return get_setting('site_cdn_protocol') . '://' . get_setting('site_cdn_url') .
      '/media/' . $compressiontype . '/' . $dimensions . '/' . $color . '/' . $file;
  }

  return get_setting('site_cdn_protocol') . '://' . get_setting('site_cdn_url') .
    '/media/' . $compressiontype . '/' . $dimensions . '/' . $file;
}

/**
 * CDN image raw
 *
 * @param array $settings
 * @return array
 */
function cdn_image_raw(array $settings)
{
  if (is_array($settings['path'])) {
    $settings['path'] = $settings['path']['path'];
  }

  if (!isset($settings['alt'])) {
    $settings['alt'] = null;
  }
  if (!isset($settings['title'])) {
    $settings['title'] = null;
  }
  if (!isset($settings['image_class'])) {
    $settings['image_class'] = null;
  }
  if (!isset($settings['image_style'])) {
    $settings['image_style'] = null;
  }
  if (!isset($settings['picture_class'])) {
    $settings['picture_class'] = null;
  }

  if (!isset($settings['fill'])) {
    $settings['fill'] = null;
  }
  $res = ['1x'];
  if (!isset($settings['resolutions'])) {
    $settings['resolutions'] = [];
  } else {
    $settings['resolutions'] = array_merge($res, $settings['resolutions']);
  }

  $cdn = get_cdn_url('');

  $output = [
    'xxs' => [
      'path' => $settings['path'],
      'resolutions' => $settings['resolutions'],
      'compression' => $settings['compression'],
      'fill' => $settings['fill'],
      'dimensions' => $settings['dimensions'],
      'maxwidth' => '320',
      'url' => null
    ],
    'xs' => [
      'path' => $settings['path'],
      'resolutions' => $settings['resolutions'],
      'compression' => $settings['compression'],
      'fill' => $settings['fill'],
      'dimensions' => $settings['dimensions'],
      'maxwidth' => '480',
      'url' => null
    ],
    'sm' => [
      'path' => $settings['path'],
      'resolutions' => $settings['resolutions'],
      'compression' => $settings['compression'],
      'fill' => $settings['fill'],
      'dimensions' => $settings['dimensions'],
      'maxwidth' => '768',
      'url' => null
    ],
    'md' => [
      'path' => $settings['path'],
      'resolutions' => $settings['resolutions'],
      'compression' => $settings['compression'],
      'fill' => $settings['fill'],
      'dimensions' => $settings['dimensions'],
      'maxwidth' => '992',
      'url' => null
    ],
    'lg' => [
      'path' => $settings['path'],
      'resolutions' => $settings['resolutions'],
      'compression' => $settings['compression'],
      'fill' => $settings['fill'],
      'dimensions' => $settings['dimensions'],
      'maxwidth' => '1200',
      'url' => null
    ],
    'xl' => [
      'path' => $settings['path'],
      'resolutions' => $settings['resolutions'],
      'compression' => $settings['compression'],
      'fill' => $settings['fill'],
      'dimensions' => $settings['dimensions'],
      'maxwidth' => '1440',
      'url' => null
    ],
    'xxl' => [
      'path' => $settings['path'],
      'resolutions' => $settings['resolutions'],
      'compression' => $settings['compression'],
      'fill' => $settings['fill'],
      'dimensions' => $settings['dimensions'],
      'maxwidth' => '1920',
      'url' => null
    ]
  ];

  if (isset($settings['xs']) && !isset($settings['xxs'])) {
    $settings['xxs'] = $settings['xs'];
  }

  foreach ($output as $size => $item) {

    if (isset($settings[$size])) {
      foreach ($settings[$size] as $alias => $value) {
        if (isset($item[$alias])) {
          $output[$size][$alias] = $value;
          $item[$alias] = $value;
        }
      }
    }

    $url = $cdn;
    $url .= 'media/';
    $url .= $item['compression'] . '/';
    $url .= $item['dimensions'] . '/';

    if ($item['fill']) {
      $url .= $item['fill'] . '/';
    }

    $url .= $item['path'];
    $url .= '?src=' . $item['maxwidth'] . 'w';

    if (count($item['resolutions'])) {
      $i = 0;
      foreach ($item['resolutions'] as $resolution) {
        $i++;
        if ($i != 1) {
          $output[$size]['url'] .= ' ,';
        }
        $output[$size]['url'] .= $url . '&res=' . $resolution . ' ' . $resolution . '
				';
      }
    } else {
      $output[$size]['url'] = $url;
    }
  }
  return [
    'srcset' => $output,
    'path' => get_cdn_media($settings['path'], $settings['dimensions'], $settings['compression'], $settings['fill'])
  ];
}

/**
 * CDN image
 *
 * @param array $settings
 * @return string
 */
function cdn_image(array $settings)
{
  $picture_class = isset($settings['picture_class']) ? $settings['picture_class'] : null;
  $image_class = isset($settings['image_class']) ? $settings['image_class'] : null;
  $image_style = isset($settings['image_style']) ? $settings['image_style'] : null;
  $path = isset($settings['path']) ? $settings['path'] : null;
  $dimensions = isset($settings['dimensions']) ? $settings['dimensions'] : null;
  $compression = isset($settings['compression']) ? $settings['compression'] : null;
  $fill = isset($settings['fill']) ? $settings['fill'] : null;
  $alt = isset($settings['alt']) ? $settings['alt'] : null;
  $title = isset($settings['title']) ? $settings['title'] : null;

  $data = cdn_image_raw($settings);
  $picture = '<picture class="' . $picture_class . '">';

  foreach ($data['srcset'] as $item) {
    $picture .= '
		<source srcset="' . $item['url'] . '" media="(max-width: ' . $item['maxwidth'] . 'px)">
		';
  }

  $picture .= '<img class="' . $image_class . '" src="' . get_cdn_media($path, $dimensions, $compression, $fill) . '" alt="' . $alt . '" title="' . $title . '" style="' . $image_style . '" />';
  $picture .= '</picture>';

  return $picture;
}

/**
 * CDN image CSS
 *
 * @param array $settings
 * @return string
 */
function cdn_image_css(array $settings)
{
  if (isset($settings['class'])) {
    $selector = '.' . $settings['class'];
  } else {
    $selector = null;
  }

  if (isset($settings['id'])) {
    if ($selector) {
      $selector = $selector . ', #' . $settings['id'];
    } else {
      $selector = '#' . $settings['id'];
    }
  }

  $fill = null;

  if (isset($settings['fill'])) {
    $fill = $settings['fill'];
  }

  $image_url = get_cdn_media($settings['path'], $settings['dimensions'], $settings['compression'], $fill);

  if (isset($settings['xs'])) {
    if (!isset($settings['xs']['compression'])) {
      $settings['xs']['compression'] = $settings['compression'];
    }
    $xs_image_url = get_cdn_media($settings['path'], $settings['xs']['dimensions'], $settings['xs']['compression'], $settings['xs']['fill']);
  } else {
    $xs_image_url = $image_url;
  }

  if (isset($settings['sm'])) {
    if (!isset($settings['sm']['compression'])) {
      $settings['sm']['compression'] = $settings['compression'];
    }

    $sm_image_url = get_cdn_media($settings['path'], $settings['sm']['dimensions'], $settings['sm']['compression'], $settings['sm']['fill']);
  } else {
    $sm_image_url = $image_url;
  }

  if (isset($settings['md'])) {
    if (!isset($settings['md']['compression'])) {
      $settings['md']['compression'] = $settings['compression'];
    }
    $md_image_url = get_cdn_media($settings['path'], $settings['md']['dimensions'], $settings['md']['compression'], $settings['md']['fill']);
  } else {
    $md_image_url = $image_url;
  }

  if (isset($settings['lg'])) {
    if (!isset($settings['lg']['compression'])) {
      $settings['lg']['compression'] = $settings['compression'];
    }

    $lg_image_url = get_cdn_media($settings['path'], $settings['lg']['dimensions'], $settings['lg']['compression'], $settings['lg']['fill']);
  } else {
    $lg_image_url = $image_url;
  }

  if (isset($settings['xl'])) {
    if (!isset($settings['xl']['compression'])) {
      $settings['xl']['compression'] = $settings['compression'];
    }

    $xl_image_url = get_cdn_media($settings['path'], $settings['xl']['dimensions'], $settings['xl']['compression'], $settings['xl']['fill']);
  } else {
    $xl_image_url = $image_url;
  }

  return '
<style scoped>

	' . $selector . ' { background-image: url(' . $image_url . '); }
	@media (max-width: 1920px) { ' . $selector . '{ background-image: url(' . $image_url . '?src=1920w); }}
	@media (max-width: 1440px) { ' . $selector . '{ background-image: url(' . $xl_image_url . '?src=1440w); }}
	@media (max-width: 1200px) { ' . $selector . '{ background-image: url(' . $lg_image_url . '?src=1200w); }}
	@media (max-width: 992px) { ' . $selector . '{ background-image: url(' . $md_image_url . '?src=992w); }}
	@media (max-width: 768px) { ' . $selector . '{ background-image: url(' . $sm_image_url . '?src=768w); }}
	@media (max-width: 480px) { ' . $selector . '{ background-image: url(' . $xs_image_url . '?src=480w); }}
	@media (max-width: 320px) { ' . $selector . '{ background-image: url(' . $xs_image_url . '?src=320w); }}

	@media
	(-webkit-min-device-pixel-ratio: 1.5),
	(   min--moz-device-pixel-ratio: 1.5),
	(     -o-min-device-pixel-ratio: 3/2),
	(        min-device-pixel-ratio: 1.5),
	(                min-resolution: 1.5dppx) {


		' . $selector . ' { background-image: url(' . $image_url . '); }
		@media (max-width: 1920px) { ' . $selector . '{ background-image: url(' . $image_url . '?src=1920w&res=2x); }}
		@media (max-width: 1440px) { ' . $selector . '{ background-image: url(' . $xl_image_url . '?src=1440w&res=2x); }}
		@media (max-width: 1200px) { ' . $selector . '{ background-image: url(' . $lg_image_url . '?src=1200w&res=2x); }}
		@media (max-width: 992px) { ' . $selector . '{ background-image: url(' . $md_image_url . '?src=992w&res=2x); }}
		@media (max-width: 768px) { ' . $selector . '{ background-image: url(' . $sm_image_url . '?src=768w&res=2x); }}
		@media (max-width: 480px) { ' . $selector . '{ background-image: url(' . $xs_image_url . '?src=480w&res=2x); }}
		@media (max-width: 320px) { ' . $selector . '{ background-image: url(' . $xs_image_url . '?src=320w&res=2x); }}

	}

</style>
	';
}
