<?php

namespace PXO\Annotation;

/**
 * @Annotation
 */
final class Element extends Annotation {

  /**
   * Collapse the Element to a string with the value of this attribute
   *
   * @example <element attribute="value"/> => element=value
   *
   * @var string $attribute
   */
  public $attribute;

  /**
   * If the property should contain an array of elements
   *
   * @var boolean $multiple
   */
  public $multiple = FALSE;

  /**
   * The XML element to be bound to this property
   * (in Clark notation)
   *
   * @example {http://www.w3.org/2001/XMLSchema}schema
   *
   * @var string $source
   */
  public $source;

  /**
   * Type of the element usually in the form of xs:string, xs:date etc
   * Can be used to parse content of items such as xs:date into php date (and
   * back)
   *
   * @var string $type ;
   */
  public $type;

  /**
   * The inner XML element name (in Clark notation)
   *
   * @example {http://www.w3.org/2001/XMLSchema}schema
   *
   * Use to collapse a level in the hierachy
   * combine with $multiple=true to create represent repeating elements
   *
   * @example <items><item/><item/></items> would become $items = [$item,$item]
   *
   *
   * @var string $inner_type ;
   */
  public $collapse;


}
