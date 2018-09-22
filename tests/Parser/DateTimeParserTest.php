<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\Parser\DateTimeParser;

final class DateTimeParserTest extends TestCase {

  private $php_datetime;

  private $xml_datetime;

  public function testDeserialize(): void {
    $this->assertEquals(
      $this->php_datetime,
      DateTimeParser::deserialize($this->xml_datetime)
    );
    $this->assertSame(NULL,DateTimeParser::deserialize(NULL));
    $this->assertSame(NULL,DateTimeParser::deserialize(156789));
    $this->assertSame(mktime(),DateTimeParser::deserialize("now"));
  }

  public function testSerialize(): void {
    $this->assertEquals(
      $this->xml_datetime,
      DateTimeParser::serialize($this->php_datetime)
    );
    $this->assertSame(NULL,DateTimeParser::serialize('a'));
  }

  protected function setUp() {
    $this->php_datetime = mktime(10, 0, 0, 9, 10, 2018);
    $this->xml_datetime = '2018-09-10T10:00:00';
  }

}
