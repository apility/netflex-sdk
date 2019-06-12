<?php

function capture ($method, ...$arguments) {
  ob_start();
  call_user_func_array($method, $arguments);
  return ob_get_clean();
}
