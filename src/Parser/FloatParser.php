<?php

namespace PXO\Parser;

/**
 * Class FloatParser
 *
 * @package PXO\Parser
 */
class FloatParser extends BaseParser {

  /**
   * @param $xml_value
   *
   * @return float
   */
  static function deserialize($xml_value) {
    return floatval($xml_value);
  }
}
