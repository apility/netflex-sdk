<?php

/**
 * Create pretty time code
 *
 * @param string $date
 * @param string $format
 * @return string
 */
function convert_datetime($date, $format)
{
  if ($date != 0) {
    $date = new DateTime($date);
    return $date->format($format);
  }

  return null;
}

/**
 * Security and conversion variables
 *
 * @deprecated v1.1.0
 * @param string $value
 * @param string $type
 * @param string $definedValue = ""
 * @param string $undefinedValue = ""
 * @return string
 */
function convert_to_safe_string($value, $type, $definedValue = "", $undefinedValue = "")
{
  trigger_error('convert_to_safe_string is deprecated', E_USER_DEPRECATED);

  switch ($type) {
    case 'text':
      $value = ($value != '') ? "'" . $value . "'" : 'null';
    case 'str':
      $value = strip_tags($value);
      break;
    case 'long':
    case 'int':
      break;
    case 'double':
      $value = ($value != '') ? doubleval($value) : 'null';
      break;
    case 'date':
      $value = ($value != '') ? "'" . $value . "'" : 'null';
      break;
    case 'defined':
      $value = ($value != '') ? $definedValue : $undefinedValue;
      break;
    default:
      break;
  }

  return $value;
}

/**
 * Converts a Month number into the Norwegian month name
 *
 * @param string $id
 * @return void
 */
function convert_to_nor_monthname($id)
{
  $months = [
    '01' => 'Januar',
    '02' => 'Februar',
    '03' => 'Mars',
    '04' => 'April',
    '05' => 'Mai',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'August',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember',
  ];

  return $months[$id];
}
