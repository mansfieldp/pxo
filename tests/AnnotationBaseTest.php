<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\AnnotationBase;
use PXO\Annotation\Node;
use PXO\Annotation\Parser;
use PXO\Annotation\Element;
use PXO\Annotation\Attribute;
use PXO\Annotation\Text;

class AnnotationBaseTest extends TestCase {


  public function testNodeAnnotations() {
    $class = new NodeAnnotationElement();
    $this->assertSame('Test', $class->getName());
    $this->assertSame('http://example.com/Test', $class->getNamespace());
    $this->assertSame(
      '{http://example.com/Test}Test', $class->getClarkNotation()
    );

    $class2 = new NodeAnnotationElement2();
    $this->assertSame('NodeAnnotationElement2', $class2->getName());
  }

  public function testParserAnnotationUnsetValid() {
    $class = new ParserAnnotationElement();
    $this->assertFalse(array_key_exists('xs:string', $class->getParsers()));
  }

  public function testParserAnnotationUnsetInvalid() {
    $this->expectException(\Exception::class);
    $class = new ParserAnnotationElement2();
  }

  public function testParserAnnotationSetValid() {
    $class = new ParserAnnotationElement3();
    $this->assertTrue(array_key_exists('validparser', $class->getParsers()));
  }

  public function testParserAnnotationSetInvalid() {
    $this->expectException(\Exception::class);
    $class = new ParserAnnotationElement4();
  }

  public function testAttributeAnnotation() {
    $class = new Element1();
    $this->assertTrue(
      array_key_exists('attribute', $class->getAttributeAnnotations())
    );
  }

  public function testElementAnnotation() {
    $class = new Element2();
    $this->assertTrue(
      array_key_exists(
        '{http://example.com/Test}element', $class->getElementAnnotations()
      )
    );
  }

  public function testTextAnnotation() {
    $class = new Element3();
    $this->assertTrue(
      array_key_exists('text', $class->getTextAnnotations())
    );
  }

  public function testParserExists() {
    $class = new ParserAnnotationElement();

    $this->assertFalse($class->parser('xs:string'));
    $this->assertSame(
      'PXO\Parser\DateTimeParser', $class->parser('xs:dateTime')
    );



  }

  public function testDebugInfoEmpty(){
    $class = new ParserAnnotationElement();
    $debug = $class->__debugInfo();
    $this->assertTrue(is_array($debug));
    $this->assertCount(0, $debug);
  }
  public function testDebugInfoElement(){
    $class = new Element2();
    $debug = $class->__debugInfo();
    $this->assertTrue(is_array($debug));
    $this->assertCount(1, $debug);
    $this->assertTrue(array_key_exists('element',$debug));
  }
}

//MOCK Classes

/**
 * Class NodeAnnotationElement
 * @Node(name="Test",namespace="http://example.com/Test")
 */
class NodeAnnotationElement extends AnnotationBase {

}

/**
 * Class NodeAnnotationElement
 * @Node(namespace="http://example.com/Test")
 */
class NodeAnnotationElement2 extends AnnotationBase {

}

/**
 * Class NodeAnnotationElement
 * @Parser(type="xs:string",parser=NULL)
 */
class ParserAnnotationElement extends AnnotationBase {

}

/**
 * Class NodeAnnotationElement
 * @Parser(type="madeuptype",parser=NULL)
 */
class ParserAnnotationElement2 extends AnnotationBase {

}


/**
 * Class NodeAnnotationElement
 * @Parser(type="validparser",parser=PXO\Parser\BooleanParser::class)
 */
class ParserAnnotationElement3 extends AnnotationBase {

}

/**
 * Class NodeAnnotationElement
 * @Parser(type="madeuptype",parser=\Exception::class)
 */
class ParserAnnotationElement4 extends AnnotationBase {

}


/**
 * Class NodeAnnotationElement
 * @Node(name="Test",namespace="http://example.com/Test")
 */
class Element1 extends AnnotationBase {

  /**
   * @Attribute
   */
  public $attribute;
}

/**
 * Class NodeAnnotationElement
 * @Node(name="Test",namespace="http://example.com/Test")
 */
class Element2 extends AnnotationBase {

  /**
   * @Element(source="{http://example.com/Test}element")
   */
  public $element;
}

/**
 * Class NodeAnnotationElement
 * @Node(name="Test",namespace="http://example.com/Test")
 */
class Element3 extends AnnotationBase {

  /**
   * @Text
   */
  public $text;
}
