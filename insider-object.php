<?php

/**
 * Plugin Name: Insider Object Integration
 * Version: 0.1
 * Author: egekibar
 * Author URI: https://kibar.dev/
 **/

require_once 'vendor/autoload.php';

use Insider\Insider;

$object = new Insider();

$object->run();