<?php

/**
 * Plugin Name: Slider by Supsystic
 * Description: Responsive Slider by Supsystic - the ultimate slideshow solution. Create stunning image, video and content sliders with professional templates and options
 * Version: 1.2.2
 * Author: supsystic.com
 * Author URI: http://supsystic.com
 * Text Domain: supsystic-slider
 **/

require_once dirname(__FILE__) . '/app/SupsysticSlider.php';

$supsysticSlider = new SupsysticSlider();
$supsysticSlider->run();
