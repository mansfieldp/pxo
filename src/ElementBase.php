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
abstract class ElementBase extends AnnotationBase implements ElementInterface {

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
  final public function xmlSerialize(Writer $writer) {
    $this->serialize_Attributes($writer);
    $this->serialize_Elements($writer);
    $this->serialize_Text($writer);
    $this->serialize_Xml($writer);
  }

  /**
   * @param \Sabre\Xml\Writer $writer
   */
  public function serialize_Attributes(Writer $writer) {
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
  public function serialize_Text(Writer $writer) {
    foreach ($this->_text as $annotation) {
      $value = $this->{$annotation->name};
      $writer->writeRaw($value);
    }
  }

  /**
   * @param \Sabre\Xml\Writer $writer
   */
  public function serialize_Xml(Writer $writer) {
    foreach ($this->_xml as $annotation) {
      $value = $this->{$annotation->name};
      $writer->writeRaw($value);
    }
  }

  /**
   * @param \Sabre\Xml\Writer $writer
   */
  public function serialize_Elements(Writer $writer) {
    foreach ($this->_elements as $source => $annotation) {
      if (isset($this->{$annotation->name})) {
        $this->writeElement(
          $writer, $source, $this->{$annotation->name}, $annotation
        );
      }
      //@TODO optionally retain empty elements?
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
  public function writeElement(Writer $writer, $name, $element, $annotation) {
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
            [
              $name => [
                'attributes' => [
                  $annotation->attribute => $value,
                ],
              ],
            ]
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
          [
            $name => [
              'attributes' => [
                $annotation->attribute => $value,
              ],
            ],
          ]
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
  public function deserialize(Reader $reader, bool $next = TRUE) {
    $this->deserialize_Text($reader);
    $this->deserialize_Xml($reader);
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
  public function deserialize_Text(Reader $reader) {
    //Deserialize Attributes
    if (isset($this->_text)) {
      foreach ($this->_text as $name => $text) {
        $string = $reader->readString();

        $value = $this->deserializeValue($string, $text);

        $value = preg_replace("#\s+#", ' ', $value);
        $value = trim($value);

        $this->$name = $value;
      }
    }
  }

  /**
   * @param \Sabre\Xml\Reader $reader
   */
  public function deserialize_Xml(Reader $reader) {
    //Deserialize Attributes
    if (isset($this->_xml)) {
      foreach ($this->_xml as $name => $text) {
        $string = $reader->readInnerXml();
        $value = $this->deserializeValue($string, $text);

        $value = preg_replace("#\s+#", ' ', $value);
        $value = trim($value);

        $this->$name = $value;
      }
    }
  }

  /**
   * @param \Sabre\Xml\Reader $reader
   */
  public function deserialize_Attributes(Reader $reader) {
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
  public function deserialize_Elements(Reader $reader) {
    //Deserialize Elements
    $elements = $reader->parseGetElements();
    foreach ($elements as $element) {
      if (isset($this->_elements[$element['name']])) {
        $annotation = $this->_elements[$element['name']];
        $property_name = $annotation->name;
        $value = $element['value'];

        if (isset($annotation->collapse)) {
          foreach ($value as $i_element) {
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
  public function deserializeValue($value, $annotation) {
    if (isset($annotation->type)
      && !is_array($value)
      && $parser = $this->parser($annotation->type)
    ) {
      $value = $parser::deserialize($value);
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
    if (isset($annotation->type)
      && !is_array($value)
      && $parser = $this->parser($annotation->type)
    ) {
      $value = $parser::serialize($value);
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


}
