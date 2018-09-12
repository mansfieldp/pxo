<?php

namespace PXO\Parser;

/**
 * Class IntegerParser
 *
 * @package PXO\Parser
 */
class IntegerParser extends BaseParser {

  /**
   * @param $xml_value
   *
   * @return int
   */
  static function deserialize($xml_value) {
    if(is_numeric($xml_value)){
      return intval($xml_value);
    }
    return NULL;
  }

  static function serialize($php_value) {
    if (isset($php_value)) {
      return (string)intval($php_value);
    }
    return $php_value;
  }
}
