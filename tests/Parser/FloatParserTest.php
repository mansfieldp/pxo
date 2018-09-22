<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\Parser\FloatParser;

final class FloatParserTest extends TestCase {

  public function testDeserialize(): void {
    $this->assertEquals(1.2345, FloatParser::deserialize("1.2345"));
    $this->assertEquals(NULL, FloatParser::deserialize("aaaaa"));
  }

  public function testSerialize(): void {
    $this->assertEquals("1.2345", FloatParser::serialize(1.2345));
    $this->assertEquals("", FloatParser::serialize(NULL));
  }

}
