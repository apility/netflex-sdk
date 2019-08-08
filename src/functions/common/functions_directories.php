<?php

/**
 * Get entry data
 *
 * @param int $id
 * @return array|null
 */
function get_directory_entry($id)
{
  global $entry_override;
  global $revision_override;

  $entrydata = (isset($entry_override) && $entry_override == $id && isset($revision_override)) ? null : NF::$cache->fetch("entry/$id");

  if ($entrydata == null) {
    $url = 'builder/structures/entry/' . $id;
    if (isset($entry_override) && $entry_override == $id && isset($revision_override)) {
      $url .= '/revision/' . $revision_override;
    }

    try {
      $entrydata = json_decode(NF::$capi->get($url)->getBody(), true);
      if (isset($entry_override) && $entry_override == $id && isset($revision_override)) {
        $entrydata['published'] = '1';
        $entrydata['use_time'] = '0';
      }
    } catch (Exception $e) {
      return null;
    }

    $structure = NF::$site->structures[$entrydata['directory_id']];

    if ($entrydata && $entrydata['published']) {
      foreach ($entrydata as $alias => $data) {
        if ($structure['fields'][$alias]['type'] == 'image') {
          $entrydata[$alias] = $data['path'];
        }

        if ($structure['fields'][$alias]['type'] == 'gallery') {
          $return = [];
          if (count($data)) {
            foreach ($data as $item) {
              $item['image'] = $item['path'];
              $return[] = $item;
            }
            $entrydata[$alias] = $return;
          }
        }
      }

      if ($revision_override) {
        return $entrydata;
      }

      NF::$cache->save("entry/$id", $entrydata);
      NF::debug($entrydata, 'entry ' . $id . ' cached');
    }
  } else {
    NF::debug($entrydata, 'entry ' . $id . ' from memory');
  }

  if ($entrydata && ($entrydata['published']) || ($entry_override == $id && isset($revision_override))) {
    return $entrydata;
  }
}

/**
 * Resolve entry by attributes
 *
 * @param array $attributes
 * @param int $limit = 0
 * @return int
 */
function resolve_entry(array $attributes, $limit = 0)
{

  try {
    $id = json_decode(NF::$capi->post('builder/structures/entry/resolve', ['json' => $attributes])->getBody());
  } catch (Exception $e) {
    return null;
  }

  return $id;
}

/**
 * Get entry
 *
 * Alias for get_directory_entry
 * @param int $id
 * @return array
 */
function get_entry($id)
{
  return get_directory_entry($id);
}

/**
 * Get latest directory content by directory id.
 * Valid order: published, updated, start, stop, url, name, userid
 *
 * @param int $directory_id
 * @param string $orderlimit
 * @return array
 */
function get_latest_entries($directory_id, $orderlimit)
{

  $order = 'id';
  $dir = 'asc';
  $limit = 0;

  $query = [
    'directory_id:' . $directory_id,
    'published:1',
    'use_time:0 OR (use_time:1 AND start:[* TO "' . date('Y-m-d H:i:s') . '"] AND stop:["' . date('Y-m-d H:i:s') . '" TO *])'
  ];

  $matches = null;
  if (preg_match('/ORDER BY ([a-zA-Z]+) ([a-zA-Z]+)?/i', $orderlimit, $matches)) {
    $order = $matches[1];
    $dir = strtolower($matches[2]);

    if (in_array($order, ['name', 'url'])) {
      $order .= '.raw';
    }
  }

  $matches = null;
  if (preg_match('/LIMIT ([0-9]+)/i', $orderlimit, $matches)) {
    $limit = intval($matches[1]);
  }

  $matches = null;
  if (preg_match_all("/([a-zA-Z]+) ?(!?=) ?['\"]?([a-zA-Z]+)['\"]?/i", $orderlimit, $matches)) {
    for ($i = 1; $i < count($matches); $i++) {
      $operator = $matches[$i][1] === '!=' ? '-' : '';
      $query[] = $operator . $matches[$i][0] . ':' . $matches[$i][2];
    }
  }

  $query = '(' . implode(') AND (', $query) . ')';

  $query = [
    "_source" => ['id'],
    "index" => [
      "relation" => "entry",
      "relation_id" => $directory_id
    ],
    "body" => [
      "query" => [
        "query_string" => [
          "query" => $query
        ]
      ],
      "sort" => [
        [$order => $dir]
      ]
    ]
  ];

  $query['size'] = 100;

  if ($limit) {
    $query['size'] = $limit;
    $query['from'] = 0;
  }

  $result = json_decode(NF::$capi->post('search/raw', [
    'json' => $query
  ])->getBody(), true)['hits']['hits'];

  return array_map(function ($entry) {
    return $entry['_source']['id'];
  }, $result);
}

/**
 * Get full directory
 *
 * @param int $directory_id
 * @param array $order = []
 * @return array
 */
function get_full_directory($directory_id, $order = [])
{
  $data = NF::$cache->fetch("structure/$directory_id/entries");
  if ($data == null) {
    $request = NF::$capi->get('builder/structures/' . $directory_id . '/entries');
    $items = json_decode($request->getBody(), true);
    $output = [];
    foreach ($items as $item) {
      if ($item['published']) {
        $output[] = $item;
      }
    }
    $data = MultiSort($output, $order);
    NF::$cache->save("structure/$directory_id/entries", $data);
  }

  return $data;
}

/**
 * Get entry variants
 *
 * @param int $entry_id
 * @return array
 */
function get_entry_variants($entry_id)
{
  $entry = get_directory_entry($entry_id);
  return $entry['variants'];
}

/**
 * Get entry variant data
 *
 * @param int $variant_id
 * @return array
 */
function get_entry_variant($variant_id)
{
  $request = NF::$capi->get('builder/structures/variant/' . $variant_id);
  return json_decode($request->getBody(), true);
}

/**
 * Get list of content in entry. Used for galleries
 *
 * @param int $entry_id
 * @param string $area
 * @param string $content_type
 * @return array
 */
function get_entry_content_list($entry_id, $area, $content_type)
{

  $entry = get_directory_entry($entry_id);
  $data = $entry[$area];
  $contentList = [];

  if ($entry) {
    foreach ($data as $item) {
      $contentList[] = $item[$content_type];
    }
  }

  return $contentList;
}

/**
 * Get the id of an entry based on URL slug
 *
 * @param string $url
 * @param int $directory_id = null
 * @return array
 */
function get_entry_id($url, $directory_id = null)
{
  return resolve_entry([
    'url' => $url,
    'directory_id' => $directory_id
  ]);
}

/**
 * Get an entry of an id based on X, order by Y
 *
 * @param string $query
 * @param string $order
 * @return void
 */
function get_entry_id_extended($query, $order)
{

  $query = explode('AND', $query);

  foreach ($query as &$item) {
    $exp = preg_split("/(=|LIKE)/", $item);

    $field = trim($exp[0]);
    $value = str_replace("'", "", trim($exp[1]));
    $value = str_replace("%", "*", $value);

    if ($field == 'DATE(created)') {
      $field = 'created';
    }

    if ($field == 'created LIKE') {
      $field = 'created';
    }

    if ($field == 'DATE(updated)') {
      $field = 'created';
    }

    $item = $field . ':"' . $value . '"';
  }

  $query = implode(' AND ', $query);
  NF::debug($query, 'Get entry id extended query');

  $query = [
    "_source" => ['id'],
    "index" => 'entry',
    "body" => [
      "query" => [
        "query_string" => [
          "query" => $query
        ]
      ],
      "sort" => [
        ['id' => 'desc']
      ]
    ],
    "size" => 1,
    "from" => 0
  ];

  try {
    return json_decode(NF::$capi->post('search/raw', ['json' => $query])->getBody())->hits->hits[0]->_source->id;
  } catch (Exception $e) {
    return null;
  }
}

/**
 * Get custom data from entry. Standard content, not custom content.
 *
 * @param int $id
 * @param string $field
 * @return array
 */
function get_entry_data($id, $field)
{
  $entry = get_directory_entry($id);
  return $entry[$field];
}
