<?php

/**
 * Get settings
 *
 * @param string $alias
 * @return mixed
 */
function get_setting($alias)
{

  if (array_key_exists($alias, NF::$site->variables)) {
    return NF::$site->variables[$alias];
  }

  return null;
}

/**
 * Get current URL
 *
 * @return string
 */
function get_current_url()
{
  $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  return htmlentities($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES);
}

/**
 * Get redirect data
 *
 * @param string $url
 * @param string $data
 * @return mixed
 */
function get_redirect($url, $data)
{
  $redirects = NF::$cache->fetch('redirects');
  if (!is_array($redirects)) {
    $redirects = json_decode(NF::$capi->get('foundation/redirects')->getBody(), true);
    NF::$cache->save("redirects", $redirects);
  }

  $current_host = $_SERVER['HTTP_HOST'];
  $current_host_slash = $_SERVER['HTTP_HOST'] . '/';

  if (count($redirects)) {
    foreach ($redirects as $item) {
      // Check if hits on host
      if ($item['source_url'] == $current_host || $item['source_url'] == $current_host_slash) {
        return $item[$data];
      } else if ($item['source_url'] == $url) {
        return $item[$data];
      }
    }
  }

  return 0;
}

/**
 * Multisort functions for sorting large arrays. Used in get_full_directory()
 *
 * @param array $data
 * @param array $sortCriteria
 * @param bool $caseInSensitive = true
 * @return void
 */
function multisort($data, $sortCriteria, $caseInSensitive = true)
{
  $args = [];
  $i = 0;

  if (!is_array($data) || !is_array($sortCriteria)) {
    return false;
  }

  foreach ($sortCriteria as $sortColumn => $sortAttributes) {
    $colList = [];
    foreach ($data as $key => $row) {
      $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
      $rowData = $convertToLower ? strtolower($row[$sortColumn]) : $row[$sortColumn];
      $colLists[$sortColumn][$key] = $rowData;
    }
    $args[] = &$colLists[$sortColumn];

    foreach ($sortAttributes as $sortAttribute) {
      $tmp[$i] = $sortAttribute;
      $args[] = &$tmp[$i];
      $i++;
    }
  }

  $args[] = &$data;
  call_user_func_array('array_multisort', $args);

  return end($args);
}

/**
 * Create 32 character unique secret key
 *
 * @return string
 */
function create_secret_key()
{
  $datecode = date('YmdHis');
  $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
  $password = substr(str_shuffle($chars), 0, 60);

  return md5($datecode . $password);
}

/**
 * get the clients IP address
 *
 * @return string
 */
function get_client_ip()
{
  if ($_SERVER['REMOTE_ADDR']) {
    return $_SERVER['REMOTE_ADDR'];
  }

  return 'UNKNOWN';
}

/**
 * Calculates the MAC key from a set of parameters and a secret key
 *
 * @param array $msg Message array in key => value format
 * @param string $K Secret HMAC key from DIBS Admin
 * @return string
 */
function calculateMac($msg, $K)
{
  // Decode the hex encoded key
  $K = pack('H*', $K);

  // Sort the key=>value array ASCII-betically according to the key
  ksort($msg, SORT_STRING);

  // Create message from sorted array.
  $msg = urldecode(http_build_query($msg));

  // Calculate and return the SHA-256 HMAC using algorithm for 1 key
  return hash_hmac("sha256", $msg, $K);
}

/**
 * Get label
 *
 * @param string $label
 * @param string $lang = null
 * @return void
 */
function get_label($label, $lang = null)
{
  global $labels;
  global $page;

  $base64label = base64_encode($label);

  if ($lang == null) {
    $lang = $page['lang'];
  }

  if ($label != null && $lang != null) {
    if (isset(NF::$site->labels[$base64label][$lang])) {
      return NF::$site->labels[$base64label][$lang];
    }

    return $label;
  }

  return $label;
}

/**
 * Generates a password hash
 *
 * @param string $password
 * @return string
 */
function generate_hash($password)
{
  return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Generates a random string of length
 *
 * @param int $length
 * @return string
 */
function random_string($length)
{
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  return substr(str_shuffle($chars), 0, $length);
}

/**
 * Send a notification
 *
 * @param string $subject
 * @param array[mixed]string $body
 * @param array[array[string]string] $receivers
 * @param bool $log = true
 * @param string $template = null
 * @param string $reply_to = null
 * @return mixed
 */
function send_notification($subject, $body, $receivers = [], $log = true, $template = null, $reply_to = null)
{
  // Clean values
  $subject = strip_tags($subject);
  $body = base64_encode($body);

  // Set receivers
  $to = [];
  foreach ($receivers as $rec) {
    $to[]['mail'] = $rec;
  }

  // Build query
  $postdata = array(
    'subject' => $subject,
    'body' => $body,
    'to' => $to
  );

  if ($reply_to) {
    $postdata['reply_to'] = $reply_to;
  }

  $request = NF::$capi->post('relations/notifications', ['json' => $postdata]);

  if ($request->getStatusCode() == '200') {
    return 1;
  }

  return $request->getResponse();
}
