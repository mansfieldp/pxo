<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\ElementBase;


class ElementBaseTest extends TestCase {

  public function testSerializeAttributes() {
    $class = new TestAttribute();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->startDocument();
    $writer->startElement('element');

    $class->xmlSerialize($writer);
    $writer->endElement();
    $output = $writer->outputMemory();

    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL . '<element test="TestAttribute"/>',
      $output
    );

  }

  public function testSerializeElement() {
    $class = new TestElement();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(FALSE);
    $writer->startDocument();

    $writer->startElement('element');

    $class->xmlSerialize($writer);

    $writer->endElement();

    $output = $writer->outputMemory();
    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL
      . '<element><test>2018-09-22</test></element>',
      $output
    );
  }

  public function testSerializeElementMultiple() {
    $class = new TestElement2();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(FALSE);
    $writer->startDocument();

    $writer->startElement('element');

    $class->xmlSerialize($writer);

    $writer->endElement();

    $output = $writer->outputMemory();
    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL
      . '<element><test>Test1</test><test>Test2</test></element>',
      $output
    );
  }

  public function testSerializeElementMultipleAttribute() {
    $class = new TestElement3();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(FALSE);
    $writer->startDocument();

    $writer->startElement('element');

    $class->xmlSerialize($writer);

    $writer->endElement();

    $output = $writer->outputMemory();
    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL
      . '<element><test value="Test1"/><test value="Test2"/></element>',
      $output
    );
  }

  public function testSerializeElementCollapse() {
    $class = new TestElement4();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(FALSE);
    $writer->startDocument();

    $writer->startElement('element');

    $class->xmlSerialize($writer);

    $writer->endElement();

    $output = $writer->outputMemory();
    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL
      . '<element><outer><inner>Test1</inner><inner>Test2</inner></outer></element>',
      $output
    );
  }

  public function testSerializeElementCollapseAttribute() {
    $class = new TestElement5();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(FALSE);
    $writer->startDocument();

    $writer->startElement('element');

    $class->xmlSerialize($writer);

    $writer->endElement();

    $output = $writer->outputMemory();

    $this->markTestIncomplete('This needs to be implemented');
    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL
      . '<element><outer><inner value="Test1"/><inner value="Test2"/></outer></element>',
      $output
    );
  }

  public function testSerializeElementAttribute() {
    $class = new TestElement6();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(FALSE);
    $writer->startDocument();

    $writer->startElement('element');

    $class->xmlSerialize($writer);

    $writer->endElement();

    $output = $writer->outputMemory();

    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL
      . '<element><test value="AttributeText"/></element>',
      $output
    );
  }

  public function testSerializeTexts() {
    $class = new TestText();
    $writer = new \Sabre\Xml\Writer();
    $writer->openMemory();
    $writer->setIndent(FALSE);
    $writer->startDocument();

    $writer->startElement('element');

    $class->xmlSerialize($writer);

    $writer->endElement();

    $output = $writer->outputMemory();
    $this->assertSame(
      '<?xml version="1.0"?>' . PHP_EOL
      . '<element>TestString</element>',
      $output
    );

  }
}


class TestAttribute extends ElementBase {

  /**
   * @\PXO\Annotation\Attribute
   */
  public $test = "TestAttribute";

}

class TestElement extends ElementBase {

  /**
   * @\PXO\Annotation\Element(source="test", type="xs:date")
   */
  public $test;

  public function __construct() {
    parent::__construct();
    $this->test = mktime(0,0,0,9,22,2018);
  }
}

class TestElement2 extends ElementBase {

  /**
   * @\PXO\Annotation\Element(source="test",multiple=TRUE)
   */
  public $test = ["Test1", "Test2"];
}

class TestElement3 extends ElementBase {

  /**
   * @\PXO\Annotation\Element(source="test",multiple=TRUE,attribute="value")
   */
  public $test = ["Test1", "Test2"];
}

class TestElement4 extends ElementBase {

  /**
   * @\PXO\Annotation\Element(source="outer",collapse="inner")
   */
  public $test = ["Test1", "Test2"];
}

class TestElement5 extends ElementBase {

  /**
   * @\PXO\Annotation\Element(source="outer",collapse="inner",attribute="value")
   */
  public $test = ["Test1", "Test2"];
}

class TestElement6 extends ElementBase {

  /**
   * @\PXO\Annotation\Element(source="test",attribute="value")
   */
  public $test = "AttributeText";
}

class TestText extends ElementBase {

  /**
   * @\PXO\Annotation\Text
   */
  public $test = "TestString";
}
