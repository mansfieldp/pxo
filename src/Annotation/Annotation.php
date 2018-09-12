<?php

namespace PXO\Annotation;

/**
 * Class Annotation
 *
 * @package PXO
 */
abstract class Annotation implements AnnotationInterface {

  /**
   * @return string
   */
  public function __toString() {
    $vars = [];
    $class = get_called_class();
    $class_parts =explode('\\',$class);
    $class_name = trim(end($class_parts));
    foreach (get_class_vars($class) as $name => $var) {
      if (isset($this->$name)) {
        $value = $this->$name;
        if ($value === $var) {
          //If the value = default don't Set
        }
        elseif ($value === TRUE) {
          $vars[] = sprintf('%s=TRUE', $name, $value);
        }
        elseif ($value === FALSE) {
          $vars[] = sprintf('%s=FALSE', $name, $value);
        }
        else {
          $vars[] = sprintf('%s="%s"', $name, $value);
        }
      }
    }
    if($vars) {
      return '@' . $class_name . '(' . implode(',', $vars) . ')';
    }
    return '';
  }
}
