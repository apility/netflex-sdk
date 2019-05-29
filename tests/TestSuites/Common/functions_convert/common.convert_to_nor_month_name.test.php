<?php

use PHPUnit\Framework\TestCase;

final class Common_ConvertToNorMonthNameTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
  }

  public function testOutputsCorrectNames(): void
  {
    $this->assertEquals(
      convert_to_nor_monthname('01'),
      'Januar'
    );

    $this->assertEquals(
      convert_to_nor_monthname('02'),
      'Februar'
    );

    $this->assertEquals(
      convert_to_nor_monthname('03'),
      'Mars'
    );

    $this->assertEquals(
      convert_to_nor_monthname('04'),
      'April'
    );

    $this->assertEquals(
      convert_to_nor_monthname('05'),
      'Mai'
    );

    $this->assertEquals(
      convert_to_nor_monthname('06'),
      'Juni'
    );

    $this->assertEquals(
      convert_to_nor_monthname('07'),
      'Juli'
    );

    $this->assertEquals(
      convert_to_nor_monthname('08'),
      'August'
    );

    $this->assertEquals(
      convert_to_nor_monthname('09'),
      'September'
    );

    $this->assertEquals(
      convert_to_nor_monthname('10'),
      'Oktober'
    );

    $this->assertEquals(
      convert_to_nor_monthname('11'),
      'November'
    );

    $this->assertEquals(
      convert_to_nor_monthname('12'),
      'Desember'
    );
  }
}
