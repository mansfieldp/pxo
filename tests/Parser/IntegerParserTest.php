<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\Parser\IntegerParser;

final class IntegerParserTest extends TestCase {

  public function testDeserialize(): void {
    $this->assertEquals(5, IntegerParser::deserialize("5"));
    $this->assertEquals(1, IntegerParser::deserialize("1.2345"));
    $this->assertEquals(NULL, IntegerParser::deserialize("aaaaa"));
  }

  public function testSerialize(): void {
    $this->assertEquals("5", IntegerParser::serialize(5));
    $this->assertEquals("1", IntegerParser::serialize(1.2345));
    $this->assertEquals("", IntegerParser::serialize(NULL));
  }

}
