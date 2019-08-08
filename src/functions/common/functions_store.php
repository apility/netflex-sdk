<?php

/**
 * Get Order ID
 *
 * @return int
 */
function get_order_id()
{
  return get_order_id_from_secret(get_order_secret());
}

/**
 * Get Order Secret
 *
 * @return string
 */
function get_order_secret()
{
  return $_SESSION['netflex_cart'] ?? $_COOKIE['netflex_cart'] ?? null;
}

/**
 * Get Order ID from Secret
 *
 * @param string $secret
 * @return int
 */
function get_order_id_from_secret($secret)
{
  return NF::$commerce->get_order_from_secret($secret);
}

/**
 * Get Irder
 *
 * @param int $id
 * @return array
 */
function get_order($id)
{
  return NF::$commerce->get_order($id);
}

/**
 * In Stock
 *
 * @deprecated v1.1.0
 * @return int
 */
function in_stock()
{
  trigger_error('in_stock is deprecated', E_USER_DEPRECATED);

  return 1;
}

/**
 * Reset Order cache
 *
 * @param int $order_id
 * @return void
 */
function reset_order_cache($order_id)
{
  NF::$cache->delete("order/$order_id");
}

/**
 * Get number of entries in cart
 *
 * @param int $order_id
 * @return int
 */
function get_cart_entries_num($order_id)
{
  $order = get_order($order_id);
  return $order['cart']['count'];
}

/**
 * Get entries in cart
 *
 * @param int $order_id
 * @return array
 */
function get_cart_entries($order_id)
{
  $order = get_order($order_id);
  return $order['cart']['items'];
}

/**
 * Get Order Data (alias for get_order)
 *
 * @param int $order_id
 * @return array
 */
function get_order_data($order_id)
{
  return get_order($order_id);
}

/**
 * Get payment data
 *
 * @param int $order_id
 * @return array
 */
function get_payment_data($order_id)
{
  $order = get_order($order_id);
  return $order['payment'];
}

/**
 * Get order receipt number
 *
 * @param int $order_id
 * @return string
 */
function get_order_reciept_id($order_id)
{
  $order = get_order($order_id);
  return $order['order_receipt_id'];
}

/**
 * Get custom order data
 *
 * @param int $order_id
 * @return array
 */
function get_custom_order_data($order_id)
{
  $order = get_order($order_id);
  return $order['data'];
}

/**
 * Get checkout data
 *
 * @param int $order_id
 * @return array
 */
function get_checkout_data($order_id)
{
  $order = get_order($order_id);
  return $order['checkout'];
}

/**
 * Add item to cart
 *
 * @param int $order_id
 * @param int $entry_id
 * @param int $variant_id
 * @param int $no_of_entries = 1
 * @param string $entries_comments = null
 * @return mixed
 */
function add_to_cart($order_id, $entry_id, $variant_id, $no_of_entries = 1, $entries_comments = null)
{
  return NF::$commerce->cart_add([
    'entry_id' => $entry_id,
    'variant_id' => $variant_id,
    'no_of_entries' => $no_of_entries,
    'entries_comments' => $entries_comments
  ], get_order($order_id));
}

/**
 * Get Orders by Customer
 *
 * @param int $customer_id
 * @return array
 */
function get_customer_orders($customer_id)
{
  return NF::$commerce->order_get_customer_orders($customer_id);
}

/**
 * Start checkout
 *
 * @param int $order_id
 * @return void
 */
function start_checkout($order_id)
{
  $user_ip = get_client_ip();
  $user_agent = $_SERVER['HTTP_USER_AGENT'];

  NF::$commerce->order_checkout([
    'user_agent' => $user_agent,
    'ip' => $user_ip,
    'checkout_start' => date('Y-m-d H:i:s')
  ], get_order($order_id));
}
