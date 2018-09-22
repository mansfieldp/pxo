<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\Parser\DateParser;

final class DateParserTest extends TestCase {

  private $php_datetime;

  private $xml_datetime;

  public function testDeserialize(): void {
    $this->assertEquals(
      $this->php_datetime,
      DateParser::deserialize($this->xml_datetime)
    );
  }

  public function testSerialize(): void {
    $this->assertEquals(
      $this->xml_datetime,
      DateParser::serialize($this->php_datetime)
    );
  }

  protected function setUp() {
    $this->php_datetime = mktime(0, 0, 0, 9, 10, 2018);
    $this->xml_datetime = '2018-09-10';
  }
}
