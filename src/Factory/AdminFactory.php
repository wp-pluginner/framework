<?php

namespace WpPluginner\Framework\Factory;

use WpPluginner\Framework\Foundation\Factory;

use Symfony\Component\Debug\Exception\FatalThrowableError;

class AdminFactory extends Factory
{

    /**
     * addMenu
     * @return void
     */
    public function addMenu( $attributes = [], $priority = 1 )
    {
        $defaultAttributes = [
            'page_title' => $this->plugin['config']->get('plugin.namespace','WP Pluginner'),
            'menu_title' => $this->plugin['config']->get('plugin.namespace','WP Pluginner'),
            'capability' => 'read',
            'menu_slug' => $this->plugin['config']->get('plugin.slug','wp-pluginner'),
            'icon_url' => '',
            'position' => null,
            'controller' => null,
        ];

        $attributes = array_merge($defaultAttributes,$attributes);

        add_action('admin_menu', function () use ($attributes) {
            // Add shortcode support for widgets
            add_menu_page(
                $attributes['page_title'],
                $attributes['menu_title'],
                $attributes['capability'],
                $attributes['menu_slug'],
                function () use ( $attributes ){
                    return $this->executeController($attributes['controller'], $attributes);
                },
                $attributes['icon_url'],
                $attributes['position']
            );
        }, $priority);
    }

    /**
     * addSubMenu
     * @return void
     */
    public function addSubMenu( $attributes = [], $priority = 10 )
    {
        $defaultAttributes = [
            'page_title' => $this->plugin['config']->get('plugin.namespace','WP Pluginner'),
            'menu_title' => $this->plugin['config']->get('plugin.namespace','WP Pluginner'),
            'capability' => 'read',
            'menu_slug' => $this->plugin['config']->get('plugin.slug','wp-pluginner'),
            'parent_slug' => '',
            'controller' => '',
        ];

        $attributes = array_merge($defaultAttributes,$attributes);

        add_action('admin_menu', function () use ($attributes) {
            // Add shortcode support for widgets
            add_submenu_page(
                $attributes['parent_slug'],
                $attributes['page_title'],
                $attributes['menu_title'],
                $attributes['capability'],
                $attributes['menu_slug'],
                function () use ( $attributes ){
                    return $this->executeController($attributes['controller'], $attributes);
                }
            );
        }, $priority);
    }


    /**
     * addBarNode
     * @return void
     */
    public function addBarNode( $attributes = array(), $priority = 100 )
    {
        $defaultAttributes = [
            'id' => false,
            'title' => false,
            'parent' => false,
            'href' => false,
            'group' => false,
            'meta' => array(),
        ];

        $attributes = array_merge($defaultAttributes,$attributes);

        add_action('admin_bar_menu', function ($wpAdminBar) use ($attributes) {

            $wpAdminBar->add_node($attributes);

        }, $priority);
    }
}
