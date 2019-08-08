<?php

global $navData;
/** @var array Get pages for nav functions */
$navData = NF::$cache->fetch("navdata");

if ($navData == null) {
  $pages = NF::$site->pages;

  $navData = [
    'items' => [],
    'parents' => []
  ];

  foreach ($pages as $pg) {
    $navData['items'][$pg['id']] = $pg;
    $navData['parents'][$pg['parent_id']][] = $pg['id'];
  }

  NF::$cache->save("navdata", $navData);
}

/**
 * Get page ids for subpages
 *
 * @param int $page_id
 * @param string $visibility = null
 * @return array
 */
function get_nav_sub_ids($page_id, $visibility = null)
{
  $ids = [];
  $pages = NF::$site->pages;
  if ($visibility == null) {
    foreach ($pages as $pg) {
      if ($pg['parent_id'] == $page_id && $pg['visible']) {
        $ids[] = $pg['id'];
      }
    }
  } else {
    foreach ($pages as $pg) {
      if ($pg['parent_id'] == $page_id && $pg['visible'] && $pg[$visibility]) {
        $ids[] = $pg['id'];
      }
    }
  }
  return $ids;
}

/**
 * Drop down menu
 *
 * @param int $parent_id
 * @param int $levels
 * @param string $class
 * @param string $type
 * @param string $root_url = null
 * @param string $liClass = ''
 * @param string $aClass = ''
 * @return string
 */
function build_nav($parent_id, $levels, $class, $type, $root_url = null, $liClass = '', $aClass = '')
{
  global $navData;
  global $found_url;

  $i = 1;
  $nav_build = '';

  if (isset($navData['parents'][$parent_id])) {
    $nav_build = '<ul';
    $nav_build .= ' class="' . $class . '" role="menu"';
    $nav_build .= '>';

    foreach ($navData['parents'][$parent_id] as $navItem) {
      if ($navData['items'][$navItem]['visible_' . $type] == 1) {
        // External URL? Then open in exernal page
        if ($navData['items'][$navItem]['template'] == 'e') {
          $nav_url = $navData['items'][$navItem]['url'];
          if (!is_null($navData['items'][$navItem]['nav_target'])) {
            $nav_target = "target='" . $navData['items'][$navItem]['nav_target'] . "'";
          } else {
            $nav_target = "target='_blank'";
          }
        } else if ($navData['items'][$navItem]['template'] == 'i') {
          $nav_url = $root_url . '/' . get_page_data($navData['items'][$navItem]['url'], 'url');
          $nav_target = "";
        } else if ($navData['items'][$navItem]['template'] == 'f') {
          $nav_url = "#";
          $nav_target = "";
        } else if ($navData['items'][$navItem]['url'] == 'index/' || $navData['items'][$navItem]['url'] == 'index') {
          $nav_url = $root_url . '/';
          $nav_target = "";
        } else {
          $nav_url = $root_url . '/' . $navData['items'][$navItem]['url'];
          $nav_target = "";
        }

        // Navigation title
        if ($navData['items'][$navItem]['navtitle'] != '') {
          $nav_title = $navData['items'][$navItem]['navtitle'];
        } else {
          $nav_title = $navData['items'][$navItem]['name'];
        }

        $nav_class = null;

        // Check if active
        if ($found_url == $navData['items'][$navItem]['url']) {
          $nav_class .= 'active ';
        }

        // Add class navfolder if is f template
        if ($navData['items'][$navItem]['template'] == 'f') {
          $nav_class .= 'navfolder ';
        }

        if ($navData['items'][$navItem]['nav_hidden_xs']) {
          $nav_class .= 'hidden-xs ';
        }
        if ($navData['items'][$navItem]['nav_hidden_sm']) {
          $nav_class .= 'hidden-sm ';
        }
        if ($navData['items'][$navItem]['nav_hidden_md']) {
          $nav_class .= 'hidden-md ';
        }
        if ($navData['items'][$navItem]['nav_hidden_lg']) {
          $nav_class .= 'hidden-lg ';
        }

        $nav_build .= '<li class="' . $liClass . ' ' . $nav_class . '"><a class="' . $aClass . ' ' . $nav_class . '" ' . $nav_target . ' href="' . $nav_url . '" role="menuitem">' . $nav_title . '</a>';
        // find childitems recursively
        if ($levels == 2) {
          $nav_build .= build_nav($navItem, 1, "dropdown-container", $type, $root_url, $liClass, $aClass);
        } else if ($levels == 3) {
          $nav_build .= build_nav($navItem, 2, "dropdown-container", $type, $root_url, $liClass, $aClass);
        } else if ($levels == 4) {
          $nav_build .= build_nav($navItem, 3, "dropdown-container", $type, $root_url, $liClass, $aClass);
        }
        $nav_build .= '</li>';
      }
    }
    $nav_build .= '</ul>';
  }

  return $nav_build;
}

/**
 * Build breadcrumbs
 *
 * @param int $start_id = 0
 * @param int $this_page = null
 * @param string $wrapper = 'li'
 * @param string $wrapper_class = null
 * @return string
 */
function build_breadcrumb($start_id = 0, $this_page = null, $wrapper = 'li', $wrapper_class = null)
{
  $crumbs = [];
  $current_level = $this_page;
  $pages = NF::$site->pages;
  $i = 0;

  if (!is_null($this_page)) {
    while ($current_level != 0) {
      $i++;
      $data = $pages[$current_level];
      $current_level = $data['parent_id'];
      if ($data['visible'] != 0) {
        if ($data['id'] == $this_page) {
          $crumbs[] = '<' . $wrapper . ' itemprop="itemListElement" itemtype="http://schema.org/ListItem" class="active ' . $wrapper_class . '"><a class="active" href="/' . $data['url'] . '" title="' . $data['name'] . '">' . $data['name'] . '</a><meta itemprop="position" content="' . $i . '" /></' . $wrapper . '>';
        } else if ($data['published'] == 0 || $data['template'] === 'f' || $data['template'] === 'e') {
          $crumbs[] = '<' . $wrapper . ' itemprop="itemListElement" itemtype="http://schema.org/ListItem" class="unavailable ' . $wrapper_class . '">' . $data['name'] . '<meta itemprop="position" content="' . $i . '" /></' . $wrapper . '>';
        } else if ($data['template'] === 'i') {
          $crumbs[] = '<' . $wrapper . ' itemprop="itemListElement" itemtype="http://schema.org/ListItem" class="' . $wrapper_class . '"><a href="/' . get_page_data($data['url'], 'url') . '" title="' . $data['name'] . '">' . $data['name'] . '</a><meta itemprop="position" content="' . $i . '" /></' . $wrapper . '>';
        } else {
          $crumbs[] = '<' . $wrapper . ' itemprop="itemListElement" itemtype="http://schema.org/ListItem" class="' . $wrapper_class . '"><a href="/' . $data['url'] . '" title="' . $data['name'] . '">' . $data['name'] . '</a><meta itemprop="position" content="' . $i . '" /></' . $wrapper . '>';
        }
      }
    }

    $crumbs = array_reverse($crumbs);
    $crumbs = implode('', $crumbs);

    return $crumbs;
  }

  return null;
}

/**
 * Get master id for navigation
 *
 * @param string $base
 * @param int $levels
 * @return int
 */
function get_nav_base_id($base, $levels)
{
  global $page;

  $current_level = $page['id'];
  $pages = NF::$site->pages;
  $ids = [];

  while ($current_level != 0) {
    $data = $pages[$current_level];
    $ids[] = $data['id'];
    $current_level = $data['parent_id'];
  }

  if ($current_level = 0) {
    $ids[] = 0;
  }

  $level = 0;
  $total_levels = count($ids);

  if ($base === 'thispage') {
    $level = $levels;
  } else {
    $parselevel = ($total_levels - $levels);
    $level = $parselevel;
  }

  if ($level < 0) {
    return null;
  }

  return $ids[$level];
}
