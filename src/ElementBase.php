<?php

namespace PXO;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;


/**
 * Class ElementBase
 *
 * @package PXO
 */
abstract class ElementBase implements ElementInterface {

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
  private $_attributes = [];

  /**
   * Array of the contents of all Elements Annotations
   *
   * @var array
   */
  private $_elements = [];

  /**
   * Array of the contents of all Text Annotations
   *
   * @var array
   */
  private $_text = [];

  private $_name;

  private $_namespace;

  /**
   * ElementBase constructor.
   *
   * @throws \Doctrine\Common\Annotations\AnnotationException
   * @throws \ReflectionException
   */
  public function __construct() {
    $this->read_annotations();
  }

  /**
   * @throws \Doctrine\Common\Annotations\AnnotationException
   * @throws \ReflectionException
   */
  private function read_annotations() {
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Parser.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Attribute.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Element.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Text.php');
    AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Node.php');
    $reflection = new \ReflectionClass($this);
    $reader = new AnnotationReader();
    $this->read_class_annotations($reader, $reflection);
    $this->read_property_annotations($reader, $reflection);

  }

  /**
   * @param \Doctrine\Common\Annotations\AnnotationReader $reader
   * @param \ReflectionClass                              $reflection
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

        if ($property_name) {
          $annotation->name = $property->name;
          if (isset($annotation->source)) {
            $source = $annotation->source;
            if ($property_name == '_elements') {
              if ($this->_namespace && !preg_match('#{.*}.*#', $source)) {
                $source = sprintf('{%s}%s', $this->_namespace, $source);
              }
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
   * Required to Implement XmlSerializable
   * Uses Annotation(s) to control the serialization of php objects to XML
   *
   * @see  \PXO\Attribute
   * @see  \PXO\Element
   *
   * @param Writer $writer
   *
   */
  public function xmlSerialize(Writer $writer) {
    $this->serialize_Attributes($writer);
    $this->serialize_Elements($writer);
    $this->serialize_Text($writer);
  }

  /**
   * @param \Sabre\Xml\Writer $writer
   */
  protected function serialize_Attributes(Writer $writer) {
    foreach ($this->_attributes as $source => $annotation) {
      if (isset($this->{$annotation->name})) {
        $value = $this->{$annotation->name};
        $value = $this->serializeValue($value, $annotation);
        $writer->writeAttribute(
          $source, (string) $value
        );
      }
    }
  }

  /**
   * @param \Sabre\Xml\Writer $writer
   */
  protected function serialize_Text(Writer $writer) {
    foreach ($this->_text as $source => $annotation) {
      $value = $this->{$annotation->name};
      $writer->writeRaw($value);
    }
  }

  /**
   * @param \Sabre\Xml\Writer $writer
   */
  protected function serialize_Elements(Writer $writer) {
    foreach ($this->_elements as $source => $annotation) {
      if (isset($this->{$annotation->name})) {
        $this->writeElement(
          $writer, $source, $this->{$annotation->name}, $annotation
        );
      }
      else {
        $this->writeElement(
          $writer, $source, NULL, $annotation
        );
      }
    }
  }


  /**
   * Write Element out to XML bases upon content and annotation(s)
   *
   * @param \Sabre\Xml\Writer $writer
   * @param                   $name
   * @param                   $element
   * @param                   $annotation
   */
  private function writeElement(Writer $writer, $name, $element, $annotation) {
    if (isset($annotation->collapse)) {
      $i_e = [];
      foreach ($element as $e) {
        $value = $this->serializeValue($e, $annotation);

        $i_e[] = [$annotation->collapse => $value];
      }
      $writer->write([$name => $i_e]);
    }
    elseif ($annotation->multiple) {
      foreach ($element as $e) {
        $value = $this->serializeValue($e, $annotation);
        if (isset($annotation->attribute)) {
          $writer->write(
            [$name => ['attributes' => [$annotation->attribute => $value]]]
          );
        }
        else {
          $writer->write([$name => $value]);
        }
      }
    }
    elseif (isset($annotation->attribute)) {
      $value = $this->serializeValue($element, $annotation);
      if (isset($value)) {
        $writer->write(
          [$name => ['attributes' => [$annotation->attribute => $value]]]
        );
      }
    }
    else {
      $value = $this->serializeValue($element, $annotation);
      $writer->write([$name => $value]);
    }
  }

  /**
   * Uses Annotation(s) to control the serialization of XML into objects
   *
   * @see  \PXO\Attribute
   * @see  \PXO\Element
   *
   * @param \Sabre\Xml\Reader $reader
   * @param bool              $next
   */
  protected function deserialize(Reader $reader, bool $next = TRUE) {
    $this->deserialize_Text($reader);
    $this->deserialize_Attributes($reader);
    $this->deserialize_Elements($reader);
    //Keep Going
    if ($next) {
      $reader->next();
    }
  }

  /**
   * @param \Sabre\Xml\Reader $reader
   */
  protected function deserialize_Text(Reader $reader) {
    //Deserialize Attributes
    if (isset($this->_text)) {
      foreach ($this->_text as $name => $text) {
        $string = $reader->readString();

        $value = $this->deserializeValue($string, $text);
        $this->$name = $value;
      }
    }
  }

  /**
   * @param \Sabre\Xml\Reader $reader
   */
  protected function deserialize_Attributes(Reader $reader) {
    $attributes = $reader->parseAttributes();
    foreach ($attributes as $name => $value) {
      if (isset($this->_attributes[$name])) {
        $annotation = $this->_attributes[$name];
        $property_name = $annotation->name;
        $value = $this->deserializeValue($value, $annotation);
        $this->$property_name = $value;
      }
    }
  }

  /**
   * @param $reader
   */
  protected function deserialize_Elements(Reader $reader) {
    //Deserialize Elements
    $elements = $reader->parseGetElements();
    foreach ($elements as $element) {
      if (isset($this->_elements[$element['name']])) {
        $annotation = $this->_elements[$element['name']];
        $property_name = $annotation->name;
        $value = $element['value'];
        if (isset($annotation->collapse)) {
          foreach ($value as $k => $i_element) {
            $i_value = $this->deserializeValue(
              $i_element['value'], $annotation
            );

            $this->{$property_name}[] = $i_value;
          }
        }

        else {
          if ($annotation->attribute
            && array_key_exists(
              $annotation->attribute, $element['attributes']
            )
          ) {
            $value = $element['attributes'][$annotation->attribute];
          }
          $value = $this->deserializeValue($value, $annotation);

          if ($annotation->multiple) {
            $this->{$property_name}[] = $value;
          }
          else {
            $this->{$property_name} = $value;
          }

        }
      }
    }
  }

  /**
   * Parse element or attribute values from XML to php
   *
   * @uses $parsers
   *
   * @param $value
   * @param $annotation
   *
   * @return mixed
   */
  private function deserializeValue($value, $annotation) {
    if (isset($annotation->type) && !is_array($value)) {
      if (array_key_exists($annotation->type, $this->parsers)) {
        $parser = $this->parsers[$annotation->type];
        $value = $parser::deserialize($value);
      }
    }
    return $value;
  }

  /**
   * Format a php value for inclusion in XML
   *
   * @uses $parsers
   *
   * @param $value
   * @param $annotation
   *
   * @return mixed
   */
  private function serializeValue($value, $annotation) {
    if (isset($annotation->type) && !is_array($value)) {
      if (array_key_exists($annotation->type, $this->parsers)) {
        $parser = $this->parsers[$annotation->type];
        $value = $parser::serialize($value);
      }
    }
    return $value;
  }

  /**
   * Required to implement xmlDeserialize
   *
   * @see ElementBase::deserialize($reader)
   *
   * @param \Sabre\Xml\Reader $reader
   *
   * @return ElementInterface class the inheriting type
   */
  public static function xmlDeserialize(Reader $reader) {
    $called_class = get_called_class();
    /**
     * @var ElementInterface $xml
     */
    $xml = new $called_class();
    $xml->deserialize($reader);
    return $xml;
  }

  /**
   * @param $annotation
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
        trigger_error($message, E_USER_WARNING);
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
        trigger_error($message);
      }
    }
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
   * @throws \ReflectionException
   */
  public function __debugInfo() {
    $reflection = new \ReflectionClass($this);
    $properties = [];
    foreach ($reflection->getProperties() as $property) {
      $properties[$property->name] = $this->{$property->name};
    }
    return $properties;
  }
}
