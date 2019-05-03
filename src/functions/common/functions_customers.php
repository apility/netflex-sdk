<?php

/**
 * Get customer data
 *
 * @param string $username
 * @param string $data = null
 * @return array|null
 */
function get_customer_data($username, $data = null)
{
  $username = convert_to_safe_string($username, 'text');
  $data = convert_to_safe_string($data, 'str');

  try {
    $customer = json_decode(NF::$capi->get('relations/customers/customer/resolve/' . $username)->getBody(), true);
  } catch (Exception $e) {
    return null;
  }

  if ($data) {
    return $customer[$data];
  }

  return $customer;
}

/**
 * Get all customers in a group
 *
 * @param int $group
 * @return array
 */
function get_group_members($group)
{
  $group = convert_to_safe_string($group, 'int');
  $request = NF::$capi->get('relations/customers/groups/' . $group . '/customers');
  $members = json_decode($request->getBody(), true);
  return $members;
}

/**
 * Add customer to group
 *
 * @param int $customer
 * @param int $group
 * @return void
 */
function add_customer_to_group($customer, $group)
{
  $customer = convert_to_safe_string($customer, 'int');
  $group = convert_to_safe_string($group, 'int');
  $request = NF::$capi->put('relations/customers/customer/' . $customer, ['json' => ['groups' => $group]]);
  return 1;
}

/**
 * Delete a customer and his/her membeships
 *
 * @param string $user_hash
 * @return bool
 */
function delete_customer($user_hash)
{
  $user_hash = convert_to_safe_string($user_hash, 'text');

  if ($user_hash) {
    // Check that customer exists
    $customer = json_decode(NF::$capi->get('relations/customers/customer/hash/' . $user_hash)->getBody(), true);

    if (isset($customer['id'])) {
      NF::$capi->delete('relations/customers/customer/' . $customer['id']);
      return true;
    }
  }

  return false;
}

/**
 * Get all customer data
 *
 * @param int $id
 * @return array|null
 */
function get_customer($id)
{
  if ($id) {
    $data = NF::$cache->fetch('customer/' . $id);

    if ($data == null) {
      $request = NF::$capi->get('relations/customers/customer/' . $id);
      $data = json_decode($request->getBody(), true);
      NF::$cache->save('customer/' . $id, $data, 3600);
    }
    return $data;
  }
}
