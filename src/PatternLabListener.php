<?php

namespace FabbDev\SafeData;

use PatternLab\Config;
use PatternLab\Data;
use PatternLab\Listener;

/**
 * Provides a Pattern Lab listener to prevent data values from being escaped.
 *
 * @todo Add tests.
 * @todo Try with PSR-4.
 * @todo Change from MakeSafe to MarkSafe - this doesn't transform anything.
 */
class PatternLabListener extends Listener {

  /**
   * The plugin's name in the Pattern Lab configuration and composer.json.
   */
  protected const PLUGIN_NAME = 'safeData';

  /**
   * Create the listener and subscribe it to the data loaded event.
   */
  public function __construct() {
    $this->addListener('patternData.dataLoaded', 'processSafeData');
  }

  /**
   * Process values matching the MakeSafe() patterns.
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
      if (preg_match('/^MakeSafe\((.*)\)$/ms', $value, $matches)) {
        $value = new \Twig_Markup($matches[1], $charset);
      }
      elseif (preg_match('/^MakeSafe\(\)\s*>(.*)$/ms', $value, $matches)) {
        $value = new \Twig_Markup(ltrim($matches[1]), $charset);
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

}
