<?php

/**
 * Plugin Name: Slider by Supsystic
 * Description: Slider by Supsystic plugin - the ultimate slideshow solution. Create stunning image and video sliders with professional templates and options.
 * Version: 1.0.9
 * Author: supsystic.com
 * Author URI: http://supsystic.com
 * Text Domain: supsystic-slider
 **/

require_once dirname(__FILE__) . '/app/SupsysticSlider.php';

$supsysticSlider = new SupsysticSlider();
$supsysticSlider->run();
