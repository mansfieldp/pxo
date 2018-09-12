<?php

namespace PXO\Parser;

/**
 * Interface BaseParserInterface
 *
 * @package PXO\Parser
 */
interface BaseParserInterface {

  /**
   * @param $php_value
   *
   * @return mixed
   */
  public static function serialize($php_value);

  /**
   * @param $xml_value
   *
   * @return mixed
   */
  public static function deserialize($xml_value);

}
