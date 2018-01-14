<?php

namespace WpPluginium\Framework\Provider;

use WpPluginium\Framework\Foundation\ServiceProvider;

use WpPluginium\Framework\Factory\AdminFactory;
use WpPluginium\Framework\Factory\AjaxFactory;
use WpPluginium\Framework\Factories\ShortcodeFactory;
use WpPluginium\Framework\Factories\WidgetFactory;

class WpServiceProvider extends ServiceProvider {

	public function register(){
		//Bind WpShortcode Class
		if (!$this->plugin->bound('plugin.shortcodes')) {
			$this->plugin->singleton('plugin.shortcodes', function () {
				return new ShortcodeFactory($this->plugin);
			});
		}
		//Bind WpWidget Class
		if (!$this->plugin->bound('plugin.widgets')) {
			$this->plugin->singleton('plugin.widgets', function () {
				return new WidgetFactory($this->plugin);
			});
		}
		//Bind WpAdmin Class
		if (!$this->app->bound('plugin.admin')) {
			$this->app->singleton('plugin.admin', function () {
				return new AdminFactory($this->plugin);
			});
		}
		//Bind WpAjax Class
		if (!$this->app->bound('plugin.ajax')) {
			$this->app->singleton('plugin.ajax', function () {
				return new AjaxFactory($this->plugin);
			});
		}
		//Loop Wordpress Directories
		foreach (array('shortcode', 'action', 'filter', 'hook', 'widget', 'admin', 'ajax') as $directoryName) {
			//Require All Php Files
			$files = $this->plugin['files']->glob($this->plugin->wp_property_path . "/{$directoryName}/*.php");

			foreach ($files as $file) {
				if (
					$directoryName == 'hook' &&
					in_array(basename($file, '.php'), ['activation','deactivation'])
				) {
					continue;
				}
				require_once $file;
			}
		}
	}
}
