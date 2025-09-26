<?php
/**
 * Plugin Name:       WordPress Plugin Shell
 * Plugin URI:        https://www.level.agency
 * Description:       A shell plugin for WordPress.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      8.0
 * License:           MIT
 * Author:            Derek Cavaliero
 * Author URI:        https://www.level.agency
 * Text Domain:       lvl:wordpress-plugin-shell
 */

namespace Lvl\WordPressPluginShell;

if (! defined('WPINC'))
  die;

class WordPressPluginShell
{
  public static $version = '1.0.0';
  private static $handle_namespace = 'lvl:wordpress-plugin-shell';
  private static $instance = null;
  
  public static function getInstance()
  {
    if (self::$instance === null)
      self::$instance = new self();

    return self::$instance;
  }

  public function __construct() 
  {
    add_action('admin_init', [$this, 'admin_init']);
  }

  public function admin_init() 
  {
    require_once __DIR__ . '/lib/Updater.php';
    $updater = new Updater(__FILE__, self::$version);
  }
}

add_action('plugins_loaded', function() {
  WordPressPluginShell::getInstance();
});