<?php

/**
 * Plugin Name: Insider Object Integration
 * Version: 1.1
 * Author: egekibar
 * Author URI: https://kibar.dev/
 **/

require_once 'vendor/autoload.php';

use Insider\Insider;

$object = new Insider();

add_filter( 'site_transient_update_plugins', [$object, 'check_plugin_update'] );
add_filter( 'transient_update_plugins', [$object, 'check_plugin_update'] );

$object->run();