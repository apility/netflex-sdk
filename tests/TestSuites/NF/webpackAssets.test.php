<?php

use Apility\WebpackAssets\WebpackAssets;
use PHPUnit\Framework\TestCase;

final class NF_WebpackAssetsTest extends TestCase {
  public function testWebpackManifestPresentInConfig (): void {
    NF::$config = ['webpackManifest' => []];

    $webpackAssets = NF::setWebpackAssets();

    $this->assertInstanceOf(WebpackAssets::class, $webpackAssets);
    $this->assertInstanceOf(WebpackAssets::class, NF::$webpackAssets);
  }

  public function testWebpackManifestNotPresentInConfig (): void {
    NF::$config = [];

    $this->assertNull(NF::setWebpackAssets());
  }
}
