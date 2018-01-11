<?php

namespace WpPluginner\Framework\Provider;

use WpPluginner\Framework\Foundation\ServiceProvider;
use WpPluginner\Framework\Controller\DevController;

class DeveloperModeProvider extends ServiceProvider {

	public function register(){

		$this->plugin['view']->addLocation(realpath(__DIR__ . '/../resource/view/'));

		$namespace = $this->plugin['config']->get('plugin.namespace');
		$slug = str_slug($namespace . '_dev');

		if (is_admin()) {
			//Setup Admin Panel
			$this->plugin['plugin.admin']->addMenu([
				'page_title' => $namespace,
				'menu_title' => $namespace,
				'capability' => 'manage_options', //$capability
				'menu_slug' => $slug, //$menu_slug
				'controller' => 'WpPluginner\Framework\Controller\DevController@show'
			]);
		}
		$this->plugin['plugin.admin']->addBarNode(
			array(
				'id' => $slug,
				'title' => $namespace,
				'href' => '?page=' . $slug . '&tab=config',
				'meta' => array('class' => 'fooBar')
			),
			100
		);
		if ($this->plugin->bound('cache')) {
			$this->plugin['plugin.admin']->addBarNode(array(
				'id' => $slug . '_flush_objects',
				'title' => 'Flush Objects',
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


		//Add Framework Cache Ajax Script to Footer
		add_action('wp_footer', function () use ($namespace, $slug){
			echo $this->plugin['view']->make('admin.framework.scripts', array('slug' => $slug));
		}, 99);
		add_action('admin_footer', function () use ($namespace, $slug) {
			echo $this->plugin['view']->make('admin.framework.scripts', array('slug' => $slug));
		}, 99);
		//Add Framework Cache Ajax Controller
		add_action("wp_ajax_" . $slug, function () {
			$controller = new DevController($this->plugin);
			return $controller->show();
		});
	}
}
