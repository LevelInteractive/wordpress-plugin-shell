<?php

namespace Lvl\WordPressPluginShell;

if (! defined('WPINC'))
  die;

class Updater
{
  private $plugin_file;
  private $plugin_slug;
  private $version;
  private $update_server = 'https://wordpress.level-agency.workers.dev';

  public function __construct($plugin_file, $version) 
  {
    $this->plugin_file = $plugin_file;
    $this->plugin_slug = basename($plugin_file, '.php');
    $this->version = $version;

    add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
    add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
  }

  public function check_for_update($transient) 
  {
    if (empty($transient->checked))
      return $transient;

    $plugin_path = plugin_basename($this->plugin_file);
        
    if (!isset($transient->checked[$plugin_path]))
      return $transient;

    $remote_version = $this->get_remote_version();
      
    if (version_compare($this->version, $remote_version, '<')) {
      $transient->response[$plugin_path] = (object) [
        'slug' => $this->plugin_slug,
        'new_version' => $remote_version,
        'package' => $this->get_download_url(),
        'url' => ''
      ];
    }

    return $transient;
  }

  private function get_remote_version() 
  {
    $url = $this->update_server . '?slug=' . $this->plugin_slug . '&version=' . $this->version;
    $response = wp_remote_get($url);

    error_log(print_r($response, true));
    
    if (is_wp_error($response))
      return false;

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    return $data['new_version'] ?? false;
  }

  private function get_download_url() 
  {
    $url = $this->update_server . '?slug=' . $this->plugin_slug . '&version=' . $this->version;
    $response = wp_remote_get($url);
    
    if (is_wp_error($response))
      return false;

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    return $data['package'] ?? false;
  }

  public function plugin_info($false, $action, $response) 
  {
    if ($action !== 'plugin_information' || $response->slug !== $this->plugin_slug)
      return $false;

    // Return plugin information for the details popup
    return (object) [
      'name' => 'My Plugin Name',
      'slug' => $this->plugin_slug,
      'version' => $this->get_remote_version(),
      'author' => 'Your Name',
      'homepage' => 'https://github.com/username/repo',
      'sections' => [
        'description' => 'Plugin description here'
      ]
    ];
  }
}