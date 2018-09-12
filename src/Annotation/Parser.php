<?php

namespace PXO\Annotation;

/**
 * Use to Annotate classes inheriting XmlElement with custom data parser(s)
 * @Annotation
 */
final class Parser extends Annotation {

  /**
   * Type of the element usually in the form of xs:string, xs:date etc
   * Can be used to parse content of items such as xs:date into php date (and back)
   *
   * @var string $type ;
   */
  public $type;

  /**
   * If the property should contain an array of elements
   *
   * @var string $parser
   */
  public $parser;

}
