<?php

namespace PXO\Parser;

/**
 * Class BooleanParser
 *
 * @package PXO\Parser
 */
class BooleanParser extends DateTimeParser {

  const TRUE = 'true';

  const FALSE = 'false';

  /**
   * @param $php_value
   *
   * @return string
   */
  static function serialize($php_value) {

    if ($php_value === NULL) {
      return "";
    }
    return $php_value ? self::TRUE : self::FALSE;
  }

  /**
   * @param $xml_value
   *
   * @return boolean
   */
  static function deserialize($xml_value) {
    if (strtolower($xml_value) == self::TRUE) {
      return TRUE;
    }
    elseif (strtolower($xml_value) == self::FALSE) {
      return FALSE;
    }
    return NULL;
  }
}
