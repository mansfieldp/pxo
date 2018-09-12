<?php

namespace PXO\Parser;

/**
 * Class DateTimeParser
 *
 * @package PXO\Parser
 */
class DateTimeParser extends BaseParser {

  /**
   *
   */
  const FORMAT = 'Y-m-d\TH:i:s';

  /**
   * @param $php_value
   *
   * @return false|mixed|null|string
   */
  static function serialize($php_value) {
    if (is_numeric($php_value)) {
      $called_class = get_called_class();
      return date($called_class::FORMAT, $php_value);
    }
    return NULL;
  }

  /**
   * @param $xml_value
   *
   * @return false|int|mixed|null
   */
  static function deserialize($xml_value) {
    if ($php_value = strtotime($xml_value)) {
      return $php_value;
    }
    return NULL;
  }
}
