<?php
namespace Netflex\Site;

class Util
{

  /**
   * Returns the codepoint of a multibyte char
   *
   * @param string $char
   * @param string $encoding
   * @return int
   */
  private function mb_ord($char, $encoding = 'UTF-8')
  {
    if ($encoding === 'UCS-4BE') {
      list(, $ord) = (strlen($char) === 4) ? @unpack('N', $char) : @unpack('n', $char);
      return $ord;
    } else {
      return $this->mb_ord(mb_convert_encoding($char, 'UCS-4BE', $encoding), 'UCS-4BE');
    }
  }

  /**
   * Encodes multibyte chars to HTML entity
   *
   * @param string $string
   * @param bool $hex
   * @param string $encoding
   * @return string
   */
  private function mb_htmlentities($string, $hex = false, $encoding = 'UTF-8')
  {
    return preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($match) use ($hex) {
      return sprintf($hex ? '&#x%X;' : '&#%d;', $this->mb_ord($match[0]));
    }, $string);
  }

  /**
   * Converts emojis to html entities
   *
   * @param string $input The input string
   * @return string
   */
  public function emojiEncode($input)
  {
    return $this->mb_htmlentities($input);
  }

  /**
   * Converts a base64 encoded image to a image resource
   *
   * Returns a GD image resource. This can be directly passed into the ImageIntervention constructor, and also
   * be directly passed into a Guzzle multipart file upload.
   * Usefull when working with canvas data from JavaScript
   *
   * @param string $base64_string A string containing a image encoded as DataURL
   * @return resource GD image resource
   */
  function base64_to_image($base64_string)
  {
    $data = base64_decode(explode(',', $base64_string)[1]);
    $data = imagecreatefromstring($data);
    return $data;
  }
}
