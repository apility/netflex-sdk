<?php

/**
 * Creates PDF from given url and downloads it
 * @param string $url
 * @param array $options
 */
function generate_pdf($url, $options = [])
{
  \Netflex\Site\PDF::make($url, $options)->generate();
}
