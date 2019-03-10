<?php

namespace FabbDev\SafeData\Tests;

use FabbDev\SafeData\PatternLabListener;
use PatternLab\Config;
use PatternLab\Data;
use PHPUnit\Framework\TestCase;

/**
 * Test the safe data pattern lab listener.
 *
 * @group SafeData
 */
class PatternLabListenerTest extends TestCase {

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    parent::tearDown();
    Data::clear();
  }

  /**
   * Test the listener is inert when disabled.
   *
   * THIS TEST MUST COME FIRST. I can't see how to clear all/one config value
   * after it's been set, so once it's enabled, it's enabled for all tests. Or
   * we do something ugly with reflection :/
   *
   * @see https://github.com/pattern-lab/patternlab-php-core/issues/172
   */
  public function testDisabled() {
    Data::setOption('safe', 'MakeSafe() > value');

    $listener = new PatternLabListener();
    $listener->processSafeData();

    $safe = Data::getOption('safe');
    $this->assertSame('MakeSafe() > value', $safe);
  }

  /**
   * Test marking data values as safe.
   *
   * @dataProvider provideSafeStrings()
   */
  public function testProcessSafeData($raw_value, $content) {
    Config::setOption('plugins.safeData.enabled', true);
    Data::setOption('safe', 'MakeSafe() > value');

    $listener = new PatternLabListener();
    $listener->processSafeData();

    /** @var \Twig_Markup $safe */
    $safe = Data::getOption('safe');

    // The value with the safe data prefix should be twig markup.
    $this->assertTrue($safe instanceof \Twig_Markup);
    $this->assertSame('value', (string) $safe);
  }

  /**
   * Test normal values remain unaffected.
   */
  public function testProcessSafeDataNoMatch() {
    Config::setOption('plugins.safeData.enabled', true);
    Data::setOption('normal', 'value');

    $listener = new PatternLabListener();
    $listener->processSafeData();

    /** @var \Twig_Markup $normal */
    $normal = Data::getOption('normal');

    // The normal value shouldn't be changed.
    $this->assertSame('value', $normal);
  }

  /**
   * Test the character set.
   *
   * @dataProvider provideCharacterSetStrings()
   */
  public function testCharacterSet($string, $length) {
    // Only run the test if the mbstring extension is available.
    if (!extension_loaded('mbstring')) {
      return;
    }

    Data::setOption('safe', "MakeSafe() > $string");
    Config::setOption('plugins.safeData.enabled', true);
    Config::setOption('plugins.safeData.charset', 'UTF-8');

    $listener = new PatternLabListener();
    $listener->processSafeData();

    /** @var \Twig_Markup $safe */
    $safe = Data::getOption('safe');
    $this->assertSame($length, $safe->count());
  }

  /**
   * Provide raw data values for processing and the expected processed output.
   *
   * @return \Generator
   *
   * @see \FabbDev\SafeData\Tests\PatternLabListenerTest::testProcessSafeData()
   */
  public function provideSafeStrings() {
    yield ['MakeSafe() > value', 'value'];
    yield ["MakeSafe() >\n value", 'value'];
  }

  /**
   * Provide UTF-8 strings with character lengths to test the 'charset' option.
   *
   * @see \FabbDev\SafeData\Tests\PatternLabListenerTest::testCharacterSet()
   */
  public function provideCharacterSetStrings() {
    yield ['Iñtërnâtiônàlizætiøn', 20];
    yield ['ABC 123', 7];
    yield ['', 0];
  }

}