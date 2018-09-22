<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\Parser\StringParser;

final class StringParserTest extends TestCase {

  public function testDeserialize(): void {
    $this->assertEquals("5", StringParser::deserialize("5"));
    $this->assertEquals("Trimming", StringParser::deserialize("Trimming    "));
    $this->assertEquals("Line Breaks between", StringParser::deserialize("Line Breaks" . PHP_EOL ." between"));
  }

  public function testSerialize(): void {
    $this->assertEquals("5", StringParser::serialize(5));
    $this->assertEquals("Words", StringParser::serialize("Words"));
  }

}
