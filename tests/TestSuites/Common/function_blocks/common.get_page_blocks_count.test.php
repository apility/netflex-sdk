<?php

use PHPUnit\Framework\TestCase;

final class Common_GetPageBlocksCountTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_blocks.php');
  }

  public function testOutputsCorrectCount(): void
  {
    NF::$site->mockContent('sections', [
      ['id' => 1],
      ['id' => 2]
    ]);

    $this->assertEquals(2, get_page_blocks_count('sections'));
  }
}
