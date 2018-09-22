<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\Parser\BooleanParser;

final class BooleanParserTest extends TestCase {


  public function testDeserialize(): void {
    $this->assertTrue(BooleanParser::deserialize("true"));
    $this->assertTrue(BooleanParser::deserialize("TRUE"));
    $this->assertFalse(BooleanParser::deserialize("false"));
    $this->assertFalse(BooleanParser::deserialize("FALSE"));
    $this->assertNull(BooleanParser::deserialize("Apple"));
  }

  public function testSerialize(): void {
    $this->assertEquals("true", BooleanParser::serialize(TRUE));
    $this->assertEquals("false", BooleanParser::serialize(FALSE));
    $this->assertEquals("", BooleanParser::serialize(NULL));
  }
}
