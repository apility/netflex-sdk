<?php

namespace Netflex;

use NF;

require_once(__DIR__ . '/helpers/array_find.php');
require_once(__DIR__ . '/helpers/object_map.php');

trait FieldMapping
{

  private $defaultFields = [
    'directory_id',
    'title',
    'url',
    'revision',
    'created',
    'updated',
    'published',
    'author',
    'userid',
    'use_time',
    'start',
    'stop',
    'tags',
    'public',
    'authgroups',
    'variants'
  ];

  public function getIdAttribute($id)
  {
    if ($this->typecasting === true) {
      return intval($id);
    }

    return $id;
  }

  public function getDirectoryIdAttribute($directory_id)
  {
    if ($this->typecasting) {
      return intval($directory_id);
    }

    return $directory_id;
  }

  public function getRevisionAttribute($revision)
  {
    if ($this->typecasting) {
      return intval($revision);
    }

    return $revision;
  }

  public function getPublishedAttribute($published)
  {
    if ($this->typecasting) {
      return $published === '1';
    }

    return $published;
  }

  public function getUserIdAttribute($user_id)
  {
    if ($this->typecasting) {
      return intval($user_id);
    }

    return $user_id;
  }

  public function getUseTimeAttribute($use_time)
  {
    if ($this->typecasting) {
      return $use_time === '1';
    }

    return $use_time;
  }

  public function getPublicAttribute($public)
  {
    if ($this->typecasting) {
      return $public === '1';
    }

    return $public;
  }

  private function getStructureFields()
  {
    $fields = [];
    $structureId = $this->directory;
    $cacheKey = 'builder_structures_' . $structureId . '_fields';

    if (NF::$cache->has($cacheKey)) {
      $fields = unserialize(NF::$cache->fetch($cacheKey));
    } else {
      $response = NF::$capi->get('builder/structures/' . $structureId . '/fields');
      $response = json_decode($response->getBody());
      $fields = $response;
      NF::$cache->save($cacheKey, serialize($fields));
    }

    return $fields;
  }

  public function getField($key, $fields = null)
  {
    $fields = $fields ? $fields : $this->getStructureFields();

    $field = array_find($fields, function ($item) use ($key) {
      return $item->alias === $key;
    });

    return $field;
  }

  public function getFieldType($key, $fields = null)
  {
    $field = $this->getField($key, $fields);

    if ($field) {
      return $field->type;
    }
  }

  private function __typeCast($key, $value, $fields = null)
  {
    if ($this->typecasting === false) {
      return $value;
    }

    $fields = $fields ? $fields : $this->getStructureFields();
    $field = $this->getField($key, $fields);

    $type = ($field && is_object($field)) ? $field->type : null;

    switch ($type) {
      case 'checkbox':
        return boolval($value);
      case 'integer':
        return intval($value);
      case 'customer':
      case 'entry':
        return !$value ? null : intval($value);
      case 'float':
        return floatval($value);
      case  'tags':
        return collect(array_filter(explode(',', $value)));
      case 'entries':
        return collect(array_filter(array_map('intval', explode(',', $value))));
      case 'select':
        if ($this->mapFieldCodes) {
          if ($field && $field->code) {
            $mappings = [];

            foreach (explode("\n", $field->code) as $mapping) {
              $data = explode(';', $mapping);
              $id = $data[0];
              $map = $data[1];
              $mapping[$id] = $map;
            }

            if (isset($mappings[$value])) {
              return $mappings[$value];
            }
          }

          return $value === '0' ? null : $value;
        }

        return $value;
      case 'text':
      case 'datetime':
        return $value;
      case 'image':
      case 'file':
        $file = (is_array($value) && empty($value)) ? null : $value;
        if ($file && isset($file->file)) {
          $file->file = intval($file->file);
        }
        return $file;
      case 'matrix':
        return array_map(function ($item) use ($field) {
          $block = array_find($field->blocks, function ($block) use ($item) {
            return $block->alias === $item->type;
          });

          return object_map($item, function ($key, $value) use ($block) {
            if ($key === 'type') {
              return $value;
            }

            return $this->__typeCast($key, $value, $block->fields);
          });
        }, $value);
      default:
        return $value;
    }
  }
}
