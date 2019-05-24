<?php
namespace Netflex\Site;

use NF;
use Exception;

class Site
{
  private $id = NULL;
  private $revision = NULL;

  public $_content = NULL;
  public $_templates = NULL;
  public $_labels = NULL;
  public $_pages = NULL;
  public $_structures = NULL;
  public $_statics = NULL;
  public $_nav = NULL;
  public $_variables = NULL;

  public function __construct($id = NULL, $revision = NULL) {
    $this->id = $id;
    $this->revision = $revision;
  }
  public function __get($name)
  {

    nf::debug($name);
    /*
      Check if there is a _$name variable, if it is set and is null
      Then perform the 'load$name' function, which populates the variable and stores it for later
    */
    $name = strtolower($name);
    if($this->{"_" . $name} === NULL && method_exists($this, "load" . $name)) {
      $this->{"_" . $name} = \NF::$cache->resolve(($name == "content" ? "page/" . $this->id : $name), 3600, function() use ($name) {
        return $this->{"load" . $name}();
      });
      return $this->{"_" . $name};
    } else {
      return $this->{"_" . $name};
    }
    
  }

  /**
   * Left to not break legacy
   */
  public function loadGlobals () {    
  }

  /**
   * Legacy function
   * 
   * @deprecated
   */
  public function loadPage($id, $revision) {
    \NF::$site = new self($id, $revision);
  }

  public function loadContent() {
    if($this->id === NULL) {
      throw new Exception("Trying to load content on an anonymous site");
    }
    try {
      $contentItems = json_decode(NF::$capi->get('builder/pages/' . $this->id . '/content' . ($this->revision ? ('/' . $this->revision) : ''))->getBody(), true);

      $ret = [];
      foreach ($contentItems as $item) {

        if (isset($this->content[$item['area']])) {

          if (!isset($this->content[$item['area']][0])) {

            $existing = $this->content[$item['area']];
            $ret[$item['area']] = null;
            $ret[$item['area']] = [];
            $ret[$item['area']][] = $existing;
          }

          $ret[$item['area']][] = $item;
        } else {
          $ret[$item['area']] = $item;
        }

        $ret['id_' . $item['id']] = $item;
      }
      return $ret;

    } catch (Exception $e) {
      return [];
    }
  }

  public function loadStatics () {
    try {
      $statics = json_decode(NF::$capi->get('foundation/globals')->getBody(), true);
      $ret = [];
      foreach ($statics as $static) {
        foreach ($static['globals'] as $global) {
          $ret[$static['alias']][$global['alias']] = $global['content'];
        }
      }
      return $ret;
    } catch (Exception $e) {
      return [];
    }
  }

  public function loadPages () {
    
    $request = NF::$capi->get('builder/pages');
    $result = json_decode($request->getBody(), true);

    $resolved_pages = [];
    if ($result) {
      foreach ($result as $page) {
        $resolved_pages[$page['id']] = $page;
      }
      return array_filter($resolved_pages, function($page) {
        global $_mode;
        return $_mode ||$page['published'];
      });
    }
  }

  public function loadNav () {
    $ret = [];
    foreach ($this->pages as $id => $page) {
      if ($page['parent_id'] == 0) {
        $ret[$id] = $page;
      }
    }
    return $ret;
  }

  public function loadVariables () {
    $variables = json_decode(NF::$capi->get('foundation/variables')->getBody(), true);
    if ($variables) {
      $ret = [];
      foreach ($variables as $variable) {
        $ret[$variable['alias']] = $variable['value'];
      }
      return $ret;
    }
    return [];
  }

  public function loadTemplates () {
    try {
      $templates = json_decode(NF::$capi->get('foundation/templates')->getBody(), true);

      $ret = [];
      foreach ($templates as $tmp) {
        if ($tmp['type'] == 'builder') {
          $ret['components'][$tmp['id']] = $tmp;
        } else if ($tmp['type'] == 'block') {
          $ret['blocks'][$tmp['id']] = $tmp;
        } else if ($tmp['type'] == 'page') {
          $ret['pages'][$tmp['id']] = $tmp;
        }
      }
      return $ret;
    } catch (Exception $e) {
      return [];
    }
  }

  public function loadLabels () {
    return json_decode(NF::$capi->get('foundation/labels')->getBody(), true);
  }

  public function loadStructures () {
    $structures = json_decode(NF::$capi->get('builder/structures/full')->getBody(), true);

    $ret = [];
    foreach ($structures as $structure) {
      $fields = $structure['fields'];
      unset($structure['fields']);
      $ret[$structure['id']] = $structure;

      foreach ($fields as $field) {
        if ($field['type'] != 'collection') {
          $ret[$structure['id']]['fields'][$field['alias']] = $field;
          $ret[$structure['id']]['fields']['id_' . $field['id']] = $field;
        }
      }
    }
    return $ret;
  }
}
