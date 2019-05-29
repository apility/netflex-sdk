<?php

use PHPUnit\Framework\TestCase;

final class Live_SetEditBtnTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/live/functions_blocks.php');
  }

  public function testSetEditBtn(): void
  {
    $this->assertEmpty(set_edit_btn([
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
    ]));
  }
}
