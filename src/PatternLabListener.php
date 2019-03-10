<?php

namespace FabbDev\SafeData;

use PatternLab\Config;
use PatternLab\Data;
use PatternLab\Listener;

/**
 * Provides a Pattern Lab listener to prevent data values from being escaped.
 */
class PatternLabListener extends Listener {

  /**
   * The plugin's name in the Pattern Lab configuration and composer.json.
   */
  const PLUGIN_NAME = 'safeData';

  /**
   * The prefix used to indicate a value should be marked safe.
   */
  protected const PREFIX = 'MarkSafe() >';

  /**
   * Create the listener and subscribe it to the data loaded event.
   */
  public function __construct() {
    $this->addListener('patternData.dataLoaded', 'processSafeData');
  }

  /**
   * Process values matching the safe data pattern.
   *
   * When found, extract the string values and ensure it won't be escaped by
   * Twig.
   */
  public function processSafeData() {
    if (!$this->getConfig('enabled')) {
      return;
    }

    // Ideally we'd read the character set from the environment somehow. It
    // doesn't seem to be possible from the event object.
    $charset = $this->getConfig('charset') ?? 'UTF-8';

    $data = Data::get();
    array_walk_recursive($data, function (&$value) use ($charset) {
      $matches = [];
      $prefix = preg_quote(static::PREFIX, '/');

      $pattern =
        '/^' .              // Match from the start.
        $prefix .           // Ensure the prefix is first.
        '(\s|\r\n|\r|\n)' . // Require a space or newline after the prefix.
        '(.*)$' .           // Match the remainder of the string.
        '/ms';              // Do multi-line match and allow `.` to match EOL.
      if (preg_match($pattern, $value, $matches)) {
        $value = new \Twig_Markup(ltrim($matches[2]), $charset);
      }
    });

    Data::replaceStore($data);
  }

  /**
   * Returns the requested plugin configuration.
   *
   * @param string $name
   *   The name of the config option in dotted form, eg. 'enabled', 'us.title'.
   *
   * @return string|false
   *   The configuration value, or false if it wasn't found.
   */
  protected function getConfig($name) {
    return Config::getOption('plugins.' . static::PLUGIN_NAME . ".$name");
  }

  /**
   * Update a data value to be marked as safe.
   *
   * @param string $value
   *   The value to be marked safe.
   *
   * @return string
   *   The updated value.
   */
  public static function markSafe($value) {
    return static::PREFIX . ' ' . $value;
  }

}
