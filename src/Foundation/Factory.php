<?php

namespace WpPluginium\Framework\Foundation;

use Symfony\Component\Debug\Exception\FatalThrowableError;

abstract class Factory
{
    protected $plugin;


    /**
     * Constructor
     * Add Plugin Container
     * @param $plugin \WpPluginium\Framework\Container
     * @return void
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    public function executeController( $controllerClass = null, $attributes = array() )
    {
        if (!$controllerClass) {
            return '';
        }
        try{
            $plugin = $this->plugin; //needed for dev controller
            list( $controller, $method ) = explode( '@', $controllerClass );
            $this->plugin->when($controller)
                ->needs('$namespace')
                ->give(function(){
                    return $this->plugin['config']->get('plugin.namespace');
                });

            $this->plugin->when($controller)
                ->needs('$attributes')
                ->give($attributes);

            $instance = $this->plugin->make($controller);
            $instance->$method();
        } catch (\Exception $e) {
            $this->plugin->reportException($e);
            $this->plugin->renderException($this->plugin['request'], $e);
        } catch (\Throwable $e) {
            $this->plugin->reportException($e = new FatalThrowableError($e));
            $this->plugin->renderException($this->plugin['request'], $e);
        }
    }
}
