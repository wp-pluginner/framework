<?php

namespace WpPluginium\Framework\Factory;

use WpPluginium\Framework\Foundation\Factory;

use Symfony\Component\Debug\Exception\FatalThrowableError;

class AjaxFactory extends Factory
{

    /**
     * addAjax
     * @return void
     */
    public function addAjax( $attributes = [], $priority = 10 )
    {
        $defaultAttributes = [
            'action' => null,
            'controller' => null,
        ];

        $attributes = array_merge($defaultAttributes,$attributes);
        if( $attributes['action'] && $attributes['controller'])
        {
            add_action('wp_ajax_' . $attributes['action'], function () use ($attributes) {
                return $this->executeController($attributes['controller'], $attributes);
            }, $priority);
        }
    }

    /**
     * addSubMenu
     * @return void
     */
     public function addAjaxNopriv( $attributes = [], $priority = 10 )
     {
         $defaultAttributes = [
             'action' => null,
             'controller' => null,
         ];

         $attributes = array_merge($defaultAttributes,$attributes);
         if( $attributes['action'] && $attributes['controller'])
         {
             add_action('wp_ajax_nopriv_' . $attributes['action'], function () use ($attributes) {
                 return $this->executeController($attributes['controller'], $attributes);
             }, $priority);
         }
     }
}
