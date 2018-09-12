<?php

namespace PXO\Parser;

/**
 * Class IntegerParser
 *
 * @package PXO\Parser
 */
class StringParser extends BaseParser {

  /**
   * @param $xml_value
   *
   * @return string
   */
  static function deserialize($xml_value) {
    $xml_value = str_replace(PHP_EOL,' ',$xml_value);
    $xml_value = preg_replace('#([ ]+)#',' ',$xml_value);
    return trim($xml_value);
  }

}
