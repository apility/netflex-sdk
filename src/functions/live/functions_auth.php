<?php

/**
 * Login checker
 *
 * @param string $username
 * @param string $password
 * @param string $groups = '99999'
 * @param string $field = 'mail'
 * @return int
 */
function check_login($username, $password, $groups = '99999', $field = 'mail')
{
  $username = strtolower($username);

  if ($groups == '99999') {
    $input = ['username' => $username, 'password' => $password, 'field' => $field];
  } else {
    $input = ['username' => $username, 'password' => $password, 'group' => $groups, 'field' => $field];
  }

  $res = NF::$capi->post('relations/customers/auth', ['json' => $input]);
  $data = $res->getBody();

  $auth = json_decode($data, true);

  if ($auth['authenticated'] === true) {
    $customer_id = $auth['passed']['customer_id'];
    $customerd = NF::$capi->get('relations/customers/customer/' . $customer_id);
    $customerdata = json_decode($customerd->getBody(), true);

    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    // Set user data in session
    $_SESSION['netflex_siteuser'] = $customerdata['mail'];
    $_SESSION['netflex_siteuser_id'] = $customerdata['id'];
    $_SESSION['netflex_siteuser_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['netflex_sitename'] = $_SERVER["SERVER_NAME"];

    return 1;
  }

  return 0;
}

/**
 * Check access
 *
 * @param string $username = null
 * @param string $groups = null
 * @return int
 */
function check_access($username = null, $groups = null)
{
  if ($username != null || $groups = null) {
    try {
      $customer = json_decode(
        NF::$capi->get('relations/customers/customer/resolve/' . $username)
          ->getBody(),
        true
        );

      if (isset($customer['id']) && $groups == '99999') {
        return 1;
      }

      if (isset($customer['id']) && in_array($groups, $customer['groups'])) {
        return 1;
      }

      if (isset($customer['id'])) {
        return 2;
      }
    } catch (Exception $e) {}

    return 0;
  }
}
