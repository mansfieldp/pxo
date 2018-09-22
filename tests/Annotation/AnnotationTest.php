<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PXO\Annotation\Annotation;


final class AnnotationTest extends TestCase {

  public function testStringOutput() {
    $a = new TestAnnotation();



    $a->string = FALSE;
    $this->assertSame('@TestAnnotation(string=FALSE)', (string) $a);

    $a->string = true;
    $this->assertSame('@TestAnnotation(string=TRUE)', (string) $a);

    $a->string = 1;
    $this->assertSame('@TestAnnotation(string="1")', (string) $a);

    $a->string = 0;
    $this->assertSame('@TestAnnotation(string="0")', (string) $a);

    $a->string = 'apple';
    $this->assertSame('@TestAnnotation(string="apple")', (string) $a);

    $a->string = null;
    $this->assertSame('@TestAnnotation', (string) $a);

    $a->string = '';
    $this->assertSame('@TestAnnotation', (string) $a);
  }

  public function testDefaultTrue(): void {
    $a = new TestAnnotation();

    $a->defaultTrue = TRUE;
    $this->assertSame('@TestAnnotation', (string) $a);

    $a->defaultTrue = FALSE;
    $this->assertSame('@TestAnnotation(defaultTrue=FALSE)', (string) $a);

    $a->defaultTrue = NULL;
    $this->assertSame('@TestAnnotation', (string) $a);
  }

  public function testDefaultFalse(): void {
    $a = new TestAnnotation();

    $a->defaultFalse = TRUE;
    $this->assertSame('@TestAnnotation(defaultFalse=TRUE)', (string) $a);

    $a->defaultFalse = FALSE;
    $this->assertSame('@TestAnnotation', (string) $a);

    $a->defaultTrue = NULL;
    $this->assertSame('@TestAnnotation', (string) $a);
  }
}

class TestAnnotation extends Annotation {

  public $string;

  public $defaultTrue = TRUE;

  public $defaultFalse = FALSE;
}
