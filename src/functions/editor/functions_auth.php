<?php

/**
 * Login checker
 *
 * @param string $username
 * @param string $password
 * @param string $groups = '99999'
 * @param string $field = 'mail'
 */
function check_login($username, $password, $groups = null, $field = null)
{
  return true;
}

/**
 * Check access
 *
 * @param string $username = null
 * @param string $groups = null
 * @return int
 */
function check_access($username, $groups)
{
  return true;
}
