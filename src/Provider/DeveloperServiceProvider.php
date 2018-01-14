<?php

namespace WpPluginner\Framework\Provider;

use WpPluginner\Framework\Foundation\ServiceProvider;
use WpPluginner\Framework\Controller\DevController;

class DeveloperServiceProvider extends ServiceProvider {

	public function register(){

		$this->plugin['view']->addLocation(realpath(__DIR__ . '/../resource/view/'));

		$namespace = $this->plugin['config']->get('plugin.namespace', 'WpPluginner');
		$name = $this->plugin['config']->get('plugin.name', 'WP Pluginner');
		$slug = $this->plugin['config']->get('plugin.slug', 'wp_pluginner') . '_dev';

		if (is_admin()) {
			$this->plugin['plugin.admin']->addMenu([
				'page_title' => $name . ' Debug',
				'menu_title' => $name . ' Debug',
				'capability' => 'manage_options',
				'menu_slug' => $slug,
				'controller' => 'WpPluginner\Framework\Controller\DevController@show'
			]);
		}
		$this->plugin['plugin.admin']->addBarNode(
			array(
				'id' => $slug,
				'title' => $name . ' Debug',
				'href' => '?page=' . $slug . '&tab=config',
				'meta' => array('class' => 'fooBar')
			)
		);
		if ($this->plugin->bound('cache')) {
			$this->plugin['plugin.admin']->addBarNode(array(
				'id' => $slug . '_flush_objects',
				'title' => 'Flush Objects',
				'href' => '#',
				'parent' => $slug,
				'meta' => array(
					'class' => $slug . '_flush_objects'
				)
			));
		}
		if ($this->plugin->bound('session')) {
			$this->plugin['plugin.admin']->addBarNode(array(
				'id' => $slug . '_flush_sessions',
				'title' => 'Flush Sessions',
				'parent' => $slug,
				'href' => '#',
				'meta' => array(
					'class' => $slug . '_flush_sessions'
				)
			));
		}
		if ($this->plugin['config']->get('view.compiled')) {
			$this->plugin['plugin.admin']->addBarNode(array(
				'id' => $slug . '_flush_views',
				'title' => 'Flush Views',
				'parent' => $slug,
				'href' => '#',
				'meta' => array(
					'class' => $slug . '_flush_views'
				)
			));
		}
		$this->plugin['plugin.admin']->addBarNode(array(
			'id' => $slug . '_config',
			'title' => 'Configuration',
			'parent' => $slug,
			'href' => admin_url('admin.php?page=' . $slug . '&tab=config')
		));

		$this->plugin['plugin.ajax']->addAjax([
		    'action' => $slug,
		    'controller' => 'WpPluginner\Framework\Controller\DevController@show'
		]);


		//Add Framework Cache Ajax Script to Footer
		add_action('wp_footer', function () use ($namespace, $slug){
			echo $this->plugin['view']->make('admin.framework.scripts', array('slug' => $slug));
		}, 99);
		add_action('admin_footer', function () use ($namespace, $slug) {
			echo $this->plugin['view']->make('admin.framework.scripts', array('slug' => $slug));
		}, 99);
	}
}
