<?php

namespace TheUniproGroup\SafeData\Tests;

use Twig_Markup;
use TheUniproGroup\SafeData\PatternLabListener;
use PatternLab\Config;
use PatternLab\Data;
use PHPUnit\Framework\TestCase;

/**
 * Test the safe data pattern lab listener.
 *
 * @coversDefaultClass \TheUniproGroup\SafeData\PatternLabListener
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
    Data::setOption('safe', 'MarkSafe() > value');

    $listener = new PatternLabListener();
    $listener->processSafeData();

    $safe = Data::getOption('safe');
    $this->assertSame('MarkSafe() > value', $safe);
  }

  /**
   * Test marking data values as safe.
   *
   * @covers ::processSafeData
   * @dataProvider provideSafeStrings()
   */
  public function testProcessSafeData($raw_value, $content) {
    Config::setOption('plugins.safeData.enabled', true);
    Data::setOption('safe', $raw_value);

    $listener = new PatternLabListener();
    $listener->processSafeData();

    /** @var \Twig_Markup $safe */
    $safe = Data::getOption('safe');

    // The value with the safe data prefix should be twig markup.
    $this->assertSame($content, (string) $safe);
    $this->assertTrue($safe instanceof Twig_Markup);
  }

  /**
   * Test normal values remain unaffected.
   *
   * @covers ::processSafeData
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
   * @covers ::processSafeData
   * @dataProvider provideCharacterSetStrings()
   */
  public function testCharacterSet($string, $length) {
    // Only run the test if the mbstring extension is available.
    if (!extension_loaded('mbstring')) {
      return;
    }

    Data::setOption('safe', "MarkSafe() > $string");
    Config::setOption('plugins.safeData.enabled', true);
    Config::setOption('plugins.safeData.charset', 'UTF-8');

    $listener = new PatternLabListener();
    $listener->processSafeData();

    /** @var \Twig_Markup $safe */
    $safe = Data::getOption('safe');
    $this->assertSame($length, $safe->count());
  }

  /**
   * Test the static markSafe() method will cause a value to be marked safe.
   *
   * @covers ::markSafe
   */
  public function testMarkSafe() {
    Data::setOption('safe', PatternLabListener::markSafe('value'));
    Config::setOption('plugins.safeData.enabled', true);

    $listener = new PatternLabListener();
    $listener->processSafeData();

    /** @var \Twig_Markup $safe */
    $safe = Data::getOption('safe');
    // The value with the safe data prefix should be twig markup.
    $this->assertSame('value', (string) $safe);
    $this->assertTrue($safe instanceof Twig_Markup);
  }

  /**
   * Provide raw data values for processing and the expected processed output.
   *
   * @return \Generator
   *
   * @see \TheUniproGroup\SafeData\Tests\PatternLabListenerTest::testProcessSafeData()
   */
  public function provideSafeStrings() {
    yield ['MarkSafe() > value', 'value'];
    yield ["MarkSafe() >\n value", 'value'];
    yield ["MarkSafe() >\nline1\nline2", "line1\nline2"];
  }

  /**
   * Provide UTF-8 strings with character lengths to test the 'charset' option.
   *
   * @see \TheUniproGroup\SafeData\Tests\PatternLabListenerTest::testCharacterSet()
   */
  public function provideCharacterSetStrings() {
    yield ['Iñtërnâtiônàlizætiøn', 20];
    yield ['ABC 123', 7];
    yield ['', 0];
  }

}
