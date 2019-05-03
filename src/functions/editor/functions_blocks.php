<?php

/**
 * Set Edit Button
 *
 * @param array $settings
 * @param string $position = 'topright'
 * @return string
 */
function set_edit_btn($settings = [], $position = 'topright')
{
  global $blockhash;
  global $page_id;
  global $_mode;

  if ($_mode === 'preview') {
    return;
  }

  if (!is_null($settings['alias'])) {
    $settings['alias'] = $settings['alias'] . '_' . $blockhash;
    $settings['max-items'] = $settings['max-items'] ?? 99999;

    $position = $position ?? 'topright';

    if (!is_null($settings['config'])) {
      $config = base64_encode(serialize($settings['config']));
    }

    $class = convert_to_safe_string($settings['class'], 'str');
    $style = convert_to_safe_string($settings['style'], 'str');

    if ($settings['label'] != null) {
      $btntitle = $settings['label'];
    } else {
      $btntitle = $settings['name'];
    }

    if ($settings['icon'] != null) {
      $btnicon = '<span class="' . $settings['icon'] . '"></span> ';
    } else {
      $btnicon = null;
    }

    return '<a href="#" class="netflex-content-settings-btn netflex-content-btn-pos-' . $position . ' ' . $class . '" style="' . $style . '" data-area-name="' . $settings['name'] . '" data-area-field="' . $settings['content_field'] . '" data-area-description="' . $settings['description'] . '" data-page-id="' . $page_id . '" data-area-config="' . $config . '" data-area-type="' . $settings['type'] . '" data-area-alias="' . $settings['alias'] . '" data-max-items="' . $settings['max-items'] . '">' . $btnicon . '' . $btntitle . '</a>';
  }
}
