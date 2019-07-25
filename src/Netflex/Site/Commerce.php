<?php

namespace Netflex\Site;

use NF;

class Commerce
{

  public function __construct()
  {

    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    if (isset($_SESSION['netflex_cart'])) {
      $secret = $_SESSION['netflex_cart'];
    } else if (isset($_COOKIE['netflex_cart'])) {
      $secret = $_COOKIE['netflex_cart'];
    } else {
      $secret = null;
    }
    $this->orderSecret = $secret;
  }

  public function reset()
  {
    $_SESSION['netflex_cart'] = null;
    $_COOKIE['netflex_cart'] = null;
    unset($_SESSION['netflex_cart']);
    unset($_COOKIE['netflex_cart']);
    $this->orderSecret = null;
  }

  public function get_order_from_secret($secret = null)
  {

    if ($secret == null) {
      $secret = $this->orderSecret;
    }
    NF::debug($secret, 'Order secret');
    if ($secret) {
      $order = NF::$cache->fetch("order/$secret");
      if ($order == null) {
        $request = NF::$capi->get('commerce/orders/secret/' . $secret);
        $order = json_decode($request->getBody(), true);
        NF::$cache->save("order/$secret", $order);
      }
      return $order;
    } else {
      return null;
    }
  }

  public function get_order($id)
  {

    $order = NF::$cache->fetch("order/$id");

    if ($order == null) {
      $request = NF::$capi->get('commerce/orders/' . $id);
      $order = json_decode($request->getBody(), true);
      NF::$cache->save("order/$id", $order);
    }

    return $order;
  }

  public function reset_order_cache($order)
  {

    NF::$cache->delete('order/' . $order['id']);
    NF::$cache->delete('order/' . $order['secret']);
  }

  public function cart_add(array $cart_item, $order = null)
  {

    if ($order == null) {
      if (isset($_SESSION['netflex_siteuser_id'])) {
        $customer_id = $_SESSION['netflex_siteuser_id'];
        $request = NF::$capi->post('commerce/orders', ['json' => ['customer_id' => $customer_id]]);
      } else {
        $customer_id = 0;
        $request = NF::$capi->post('commerce/orders');
      }
      $createOrder = json_decode($request->getBody(), true);
      $order = $this->get_order($createOrder['order_id']);
      $_SESSION['netflex_cart'] = $createOrder['secret'];
    }

    $cart = $order['cart']['items'];
    if (count($cart)) {
      foreach ($cart as $item) {
        if ($cart_item['entries_comments'] == null && $cart_item['properties'] == null) {
          if (($cart_item['entry_id'] == $item['entry_id']) && ($cart_item['variant_id'] == $item['variant_id'])) {
            $found_id = $item['id'];
            $current_amount = $item['no_of_entries'];
            $create = 0;
          } else {
            $create = 1;
          }
        } else {
          $create = 1;
        }
      }
    } else {
      $create = 1;
    }

    if ($create) {
      $cart_item['ip'] = get_client_ip();
      $cart_item['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      NF::$capi->post('commerce/orders/' . $order['id'] . '/cart', ['json' => $cart_item]);
    } else {
      $cart_item['changed_in_cart'] = 1;
      if (!isset($cart_item['reset_quantity'])) {
        $cart_item['no_of_entries'] = $current_amount + $cart_item['no_of_entries'];
      }
      NF::$capi->put('commerce/orders/' . $order['id'] . '/cart/' . $found_id, ['json' => $cart_item]);
    }

    $this->reset_order_cache($order);
    return $createOrder;
  }

  public function create($customer_id = 0)
  {

    if ($customer_id) {
      $request = NF::$capi->post('commerce/orders', ['json' => ['customer_id' => $customer_id]]);
    } else {
      $request = NF::$capi->post('commerce/orders');
    }

    $createOrder = json_decode($request->getBody(), true);
    $order = $this->get_order($createOrder['order_id']);

    $_SESSION['netflex_cart'] = $createOrder['secret'];
    $this->orderSecret = $createOrder['secret'];
    $this->reset_order_cache($order);

    return $order;
  }

  public function update_cart_item($order, $item_id, $data)
  {
    $request = NF::$capi->put('commerce/orders/' . $order['id'] . '/cart/' . $item_id, ['json' => $data]);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function delete_cart_item($order, $item_id)
  {
    $response = NF::$capi->delete('commerce/orders/' . $order['id'] . '/cart/' . $item_id);
    $this->reset_order_cache($order);
  }

  public function delete_cart($order)
  {
    $response = NF::$capi->delete('commerce/orders/' . $order['id'] . '/cart');
    $this->reset_order_cache($order);
  }

  public function order_get_customer_orders($customer_id)
  {
    $request = NF::$capi->get('commerce/orders/customer/' . $customer_id);
    return json_decode($request->getBody(), true);
  }

  public function order_checkout(array $data, $order)
  {
    $request = NF::$capi->put('commerce/orders/' . $order['id'] . '/checkout', ['json' => $data]);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function order_add_payment(array $data, $order)
  {
    $request = NF::$capi->post('commerce/orders/' . $order['id'] . '/payment', ['json' => $data]);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function order_log(array $data, $order)
  {
    $request = NF::$capi->post('commerce/orders/' . $order['id'] . '/log', ['json' => $data]);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function order_update(array $data, $order)
  {
    $request = NF::$capi->put('commerce/orders/' . $order['id'], ['json' => $data]);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function order_register($order)
  {
    $request = NF::$capi->put('commerce/orders/' . $order['id'] . '/register');
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function order_data(array $data, $order)
  {
    $request = NF::$capi->put('commerce/orders/' . $order['id'] . '/data', ['json' => $data]);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function order_send_document(array $data, $document, $order)
  {
    $request = NF::$capi->post('commerce/orders/' . $order['id'] . '/document/' . $document, ['json' => $data]);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }

  public function order_get_document($document, $order)
  {
    $request = NF::$capi->get('commerce/orders/' . $order['id'] . '/document/' . $document);
    $this->reset_order_cache($order);
    return json_decode($request->getBody(), true);
  }
}
