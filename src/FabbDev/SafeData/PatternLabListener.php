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
   * Create the listener and subscribe it to the data loaded event.
   */
  public function __construct() {
    $this->addListener('patternData.dataLoaded','processSafeData');
  }

  /**
   * Process values matching the MakeSafe() patterns.
   *
   * When found, extract the string values and ensure it won't be escaped by
   * Twig.
   */
  public function processSafeData() {
    if (!Config::getOption('plugins.safeData.enabled')) {
      return;
    }

    // Ideally we'd read the character set from the environment somehow. It
    // doesn't seem to be possible from the event object.
    $charset = Config::getOption('plugins.safeData.charset') ?? 'UTF-8';

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

}
