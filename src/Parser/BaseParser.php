<?php

namespace PXO\Parser;

/**
 * Class BaseParser
 *
 * @package PXO\Parser
 */
abstract class BaseParser implements BaseParserInterface {

  /**
   * @param $php_value
   *
   * @return mixed
   */
  public static function serialize($php_value) {
    $xml_value = $php_value;
    return $xml_value;
  }

  /**
   * @param $xml_value
   *
   * @return mixed
   */
  public static function deserialize($xml_value) {
    $php_value = $xml_value;
    return (string)$php_value;
  }
}

