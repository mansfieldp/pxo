<?php

namespace PXO;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;


/**
 * Class ElementBase
 *
 * @package PXO
 */
abstract class AnnotationBase {

  /**
   * List of parsers available for use
   * use class annotation Parser to add, unset or overwrite
   *
   * @var Parser\BaseParser[] $default_parsers
   */
  private $parsers
    = [
      'xs:string' => Parser\StringParser::class,
      'xs:boolean' => Parser\BooleanParser::class,
      'xs:date' => Parser\DateParser::class,
      'xs:dateTime' => Parser\DateTimeParser::class,
      'xs:decimal' => Parser\FloatParser::class,
      'xs:byte' => Parser\IntegerParser::class,
      'xs:int' => Parser\IntegerParser::class,
      'xs:integer' => Parser\IntegerParser::class,
      'xs:long' => Parser\IntegerParser::class,
      'xs:negativeInteger' => Parser\IntegerParser::class,
      'xs:nonNegativeInteger' => Parser\IntegerParser::class,
      'xs:nonPositiveInteger' => Parser\IntegerParser::class,
      'xs:positiveInteger' => Parser\IntegerParser::class,
      'xs:short' => Parser\IntegerParser::class,
      'xs:unsignedLong' => Parser\IntegerParser::class,
      'xs:unsignedInt' => Parser\IntegerParser::class,
      'xs:unsignedShort' => Parser\IntegerParser::class,
      'xs:unsignedByte' => Parser\IntegerParser::class,
    ];

  /**
   * Array of the contents of all Attribute Annotations
   *
   * @var array
   */
  protected $_attributes = [];

  /**
   * Array of the contents of all Elements Annotations
   *
   * @var array
   */
  protected $_elements = [];

  /**
   * Array of the contents of all Text Annotations
   *
   * @var array
   */
  protected $_text = [];

  /**
   * Array of the contents of all Xml Annotations
   *
   * @var array
   */
  protected $_xml = [];

  private $_name;

  private $_namespace;

  /**
   * AnnotationBase constructor.
   *
   * @throws \Exception
   * @throws \ReflectionException
   */
  public function __construct() {
    $this->read_annotations();
  }

  /**
   * @throws \Exception
   * @throws \ReflectionException
   */
  private function read_annotations() {
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Parser.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Attribute.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Element.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Text.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Xml.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Node.php');
    $reflection = new \ReflectionClass($this);
    $reader = new AnnotationReader();
    $this->read_class_annotations($reader, $reflection);
    $this->read_property_annotations($reader, $reflection);

  }

  /**
   * @param \Doctrine\Common\Annotations\AnnotationReader $reader
   * @param \ReflectionClass                              $reflection
   *
   * @throws \Exception
   */
  private function read_class_annotations(
    AnnotationReader $reader, \ReflectionClass $reflection
  ) {

    $annotations = $reader->getClassAnnotations($reflection);
    if (count($annotations)) {
      foreach ($annotations as $annotation) {
        $annotation_class = get_class($annotation);
        if ($annotation_class == Annotation\Parser::class) {
          $this->add_parsers($annotation);
        }
        elseif ($annotation_class == Annotation\Node::class) {
          if (isset($annotation->name)) {
            $this->_name = $annotation->name;
          }
          else {
            //If not set use the class name (without namespace)
            $called = get_called_class();
            $parts = explode('\\', $called);
            $this->_name = end($parts);
          }

          $this->_namespace = $annotation->namespace;
        }
      }
    }
  }

  /**
   * @param \Doctrine\Common\Annotations\AnnotationReader $reader
   * @param \ReflectionClass                              $reflection
   */
  private function read_property_annotations(
    AnnotationReader $reader, \ReflectionClass $reflection
  ) {
    $properties = $reflection->getProperties();
    foreach ($properties as $property) {
      foreach ($reader->getPropertyAnnotations($property) as $annotation) {
        $class = get_class($annotation);
        if ($class == Annotation\Attribute::class) {
          $property_name = '_attributes';
        }
        elseif ($class == Annotation\Element::class) {
          $property_name = '_elements';
        }
        elseif ($class == Annotation\Text::class) {
          $property_name = '_text';
        }
        elseif ($class == Annotation\Xml::class) {
          $property_name = '_xml';
        }

        if ($property_name) {
          $annotation->name = $property->name;
          if (isset($annotation->source)) {
            $source = $annotation->source;
            if ($property_name == '_elements'
              && $this->_namespace
              && !preg_match('#{.*}.*#', $source)
            ) {
              $source = sprintf('{%s}%s', $this->_namespace, $source);
            }
          }
          else {
            $source = $property->name;
          }
          $this->$property_name[$source] = $annotation;
        }
      }
    }
  }

  /**
   * @param $annotation
   *
   * @throws \Exception
   */
  private function add_parsers($annotation) {
    $interface_class = Parser\BaseParserInterface::class;
    if (is_null($annotation->parser)) {
      //Using NULL to unset a Parser (which it exists)
      if (array_key_exists($annotation->type, $this->parsers)) {
        unset($this->parsers[$annotation->type]);
      }
      //Using NULL to unset parser (which does not exist)
      else {
        $text
          = 'Trying to unset parser %s which is not set; while using Annotation';
        $message = sprintf($text, $annotation->type);
        throw new \Exception($message);
      }
    }
    else {
      //Adding a Parser (which implements BaseParserInterface::class)
      if (is_subclass_of($annotation->parser, $interface_class)) {
        $this->parsers[$annotation->type] = $annotation->parser;
      }
      // Failing to add a Parser (does not implement BaseParserInterface::class)
      else {
        $text
          = 'Trying to set parser %s to %s which is does not implement %s';
        $message = sprintf(
          $text,
          $annotation->type,
          $annotation->parser,
          $interface_class
        );
        throw new \Exception($message);
      }
    }
  }

  /**
   * @return array
   */
  public function getAttributeAnnotations() {
    return $this->_attributes;
  }

  /**
   * @return array
   */
  public function getElementAnnotations() {
    return $this->_elements;
  }

  /**
   * @return array
   */
  public function getTextAnnotations() {
    return $this->_text;
  }

  /**
   * @return array
   */
  public function getXmlAnnotations() {
    return $this->_xml;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * @return string
   */
  public function getNamespace() {
    return $this->_namespace;
  }

  /**
   * @return string
   */
  public function getClarkNotation() {
    return sprintf('{%s}%s', (string) $this->_namespace, (string) $this->_name);
  }

  /**
   * @return array
   */
  public function getParsers() {
    return $this->parsers;
  }

  /**
   * @return array
   * @throws \ReflectionException
   */
  public function __debugInfo() {
    $reflection = new \ReflectionClass($this);
    $properties = [];
    foreach (
      $reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property
    ) {
      if (!is_null($this->{$property->name})) {
        $properties[$property->name] = $this->{$property->name};
      }
    }
    return $properties;
  }

  /**
   * @param $type
   *
   * @return bool|\PXO\Parser\BaseParser
   */
  public function parser($type) {
    if (array_key_exists($type, $this->parsers)) {
      return $this->parsers[$type];
    }
    else {
      return FALSE;
    }
  }
}
