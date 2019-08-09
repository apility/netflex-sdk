<?php
namespace Netflex\Site;

use NF;
use Exception;

class Site
{
  public $content;
  public $templates;
  public $labels;
  public $pages;
  public $structures;
  public $statics;
  public $nav;
  public $variables;

  public function loadGlobals () {
    global $_mode;

    $this->_pages = NF::$cache->fetch('pages');
    if (!$this->_pages) {
      $this->loadPages();
      NF::$cache->save('pages', $this->_pages);
    }

    $this->pages = [];

    foreach ($this->_pages as $key => $page) {
      if (!$page['published']) {
        if ($_mode) {
          $this->pages[$key] = $page;
        }
      } else {
        $this->pages[$key] = $page;
      }
    }

    $this->nav = NF::$cache->fetch('nav');
    if ($this->nav == null) {
      $this->loadNav();
      NF::$cache->save('nav', $this->nav);
    }

    $this->variables = NF::$cache->fetch('variables');
    if ($this->variables == null) {
      $this->loadVariables();
      NF::$cache->save('variables', $this->variables);
    }

    $this->statics = NF::$cache->fetch('statics');
    if ($this->statics == null) {
      $this->loadStatics();
      NF::$cache->save('statics', $this->statics);
    }

    $this->templates = NF::$cache->fetch('templates');
    if ($this->templates == null) {
      $this->loadTemplates();
      NF::$cache->save('templates', $this->templates);
    }

    $this->loadLabels();

    $this->structures = NF::$cache->fetch('structures');
    if ($this->structures == null) {
      $this->loadStructures();
      NF::$cache->save('structures', $this->structures);
    }

    NF::$jwt = new JWT($this->variables['netflex_api']);
  }

  public function loadPage($id, $revision) {
    global $_mode;

    $this->content = NF::$cache->fetch("page/$id");
    if ($_mode) {
      $this->content = [];
      $this>-loadContent($id, $revision);
    } else if (!$this->content) {
      $this->loadContent($id, $revision);
      NF::$cache->save("page/$id", $this->content);
    }
  }


  public function loadContent($id, $revision) {
    try {
      $contentItems = json_decode(NF::$capi->get('builder/pages/' . $id . '/content' . ($revision ? ('/' . $revision) : ''))->getBody(), true);
      foreach ($contentItems as $item) {
        if ($item['published'] === '1') {
          if (isset($this->content[$item['area']])) {

            if (!isset($this->content[$item['area']][0])) {

              $existing = $this->content[$item['area']];
              $this->content[$item['area']] = null;
              $this->content[$item['area']] = [];
              $this->content[$item['area']][] = $existing;
            }

            $this->content[$item['area']][] = $item;
          } else {
            $this->content[$item['area']] = $item;
          }

          $this->content['id_' . $item['id']] = $item;
        }
      }
    } catch (Exception $e) {
      $this->content = [];
    }
  }

  public function loadStatics () {
    try {
      $statics = json_decode(NF::$capi->get('foundation/globals')->getBody(), true);

      foreach ($statics as $static) {
        foreach ($static['globals'] as $global) {
          $this->statics[$static['alias']][$global['alias']] = $global['content'];
        }
      }
    } catch (Exception $e) {
      $this->statics = [];
    }
  }

  public function loadPages () {
    $request = NF::$capi->get('builder/pages');
    $result = json_decode($request->getBody(), true);

    if ($result) {
      foreach ($result as $page) {
        $this->_pages[$page['id']] = $page;
      }
    }
  }

  public function loadNav () {
    $pages = $this->pages;
    foreach ($pages as $id => $page) {

      if ($page['parent_id'] == 0) {

        $this->nav[$id] = $page;
      }
    }
  }

  public function loadVariables () {
    $variables = json_decode(NF::$capi->get('foundation/variables')->getBody(), true);

    if ($variables) {
      foreach ($variables as $variable) {
        $this->variables[$variable['alias']] = $variable['value'];
      }
    }
  }

  public function loadTemplates () {
    try {
      $templates = json_decode(NF::$capi->get('foundation/templates')->getBody(), true);

      foreach ($templates as $tmp) {
        if ($tmp['type'] == 'builder') {
          $this->templates['components'][$tmp['id']] = $tmp;
        } else if ($tmp['type'] == 'block') {
          $this->templates['blocks'][$tmp['id']] = $tmp;
        } else if ($tmp['type'] == 'page') {
          $this->templates['pages'][$tmp['id']] = $tmp;
        }
      }
    } catch (Exception $e) {
      $this->templates = [];
    }
  }

  public function loadLabels () {
    $labels = NF::$cache->fetch('labels');

    if (!is_array($labels)) {
      $labels = json_decode(NF::$capi->get('foundation/labels')->getBody(), true);
      NF::$cache->save('labels', $labels);
    }

    $this->labels = $labels;
  }

  public function loadStructures () {
    $structures = json_decode(NF::$capi->get('builder/structures/full')->getBody(), true);

    foreach ($structures as $structure) {
      $fields = $structure['fields'];
      unset($structure['fields']);
      $this->structures[$structure['id']] = $structure;

      foreach ($fields as $field) {
        if ($field['type'] != 'collection') {
          $this->structures[$structure['id']]['fields'][$field['alias']] = $field;
          $this->structures[$structure['id']]['fields']['id_' . $field['id']] = $field;
        }
      }
    }
  }
}
