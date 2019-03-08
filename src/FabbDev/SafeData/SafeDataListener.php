<?php

namespace FabbDev\SafeData;

use PatternLab\Config;
use PatternLab\Data;
use PatternLab\Listener;

/**
 * Provides a Pattern Lab listener to modify data from the data directory.
 */
class SafeDataListener extends Listener {

  public function __construct() {
    // add listener
    $this->addListener('patternData.dataLoaded','makeSafe');
  }

  public function makeSafe() {
    if (!Config::getOption("plugins.safe-data.enabled")) {
      return;
    }

    $data = Data::get();
    array_walk_recursive($data, function (&$value) {
      $matches = [];
      if (preg_match('/^MakeSafe\((.*)\)$/', $value, $matches)) {
        $value = 'processed: ' . $matches[1];
      }
    });

    Data::replaceStore($data);
  }

}
