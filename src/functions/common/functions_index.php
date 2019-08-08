<?php

/**
 * Search
 *
 * @param string $terms
 * @param string $relation = null
 * @param int $limit = 0
 * @param string $orderby = null
 * @param string $cacheKey = null
 * @return array
 */
function search($terms, $relation = null, $limit = 0, $orderby = null, $cacheKey = null)
{
  NF::debug(['terms' => $terms, 'relation' => $relation, 'limit' => $limit, 'orderby' => $orderby], 'Search terms');

  if ($relation) {
    $endpoint = 'search/' . $relation;
  } else {
    $endpoint = 'search';
  }


  if ($cacheKey == null) {
    $searchkey = md5($terms . $relation . $limit . serialize($orderby));
  } else {
    $searchkey = $cacheKey;
  }

  $result = NF::$cache->fetch("search/$searchkey");

  if ($result == null) {
    $request = NF::$capi->post($endpoint, ['json' => ['terms' => $terms, 'limit' => $limit, 'order' => $orderby]]);
    $result = json_decode($request->getBody(), true);
    NF::$cache->save("search/$searchkey", $result, 600);
  }

  return $result;
}

/**
 * Delete index
 *
 * @param string $relation
 * @param int $relation_id
 * @return void
 */
function delete_index($relation, $relation_id)
{
  try {
    NF::$capi->delete('search/purge/item/' . $relation . '/' . $relation_id);
    return 1;
  } catch (Exception $ex) {
    NF::debug($ex->getMessage());
    return 0;
  }
}
