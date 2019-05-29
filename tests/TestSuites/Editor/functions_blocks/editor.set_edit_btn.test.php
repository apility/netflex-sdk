<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Editor_SetEditBtnTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/editor/functions_blocks.php');
    require_once('src/functions/common/functions_convert.php');
  }

  public function testSetEditBtn(): void
  {
    $this->assertMatchesSnapshot(set_edit_btn([
      'name' => 'Test button',
      'label' => 'Test',
      'description' => 'This is a test',
      'type' => 'image',
      'alias' => 'image',
      'content_field' => 'image',
      'max-items' => 9,
      'config' => null,
      'class' => 'btn btn-sm',
      'style' => 'position:absolute'
    ]), new HtmlDriver);
  }
}
