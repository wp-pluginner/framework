<?php

namespace WpPluginium\Framework\Provider;

use WpPluginium\Framework\Foundation\ServiceProvider;
use WpPluginium\Framework\Support\PluginOptions;

class PluginOptionsServiceProvider extends ServiceProvider
{
    public function register(){
        $this->plugin->singleton('plugin.option', function ($plugin) {
            $option = new PluginOptions($plugin['config']->get('options',[]));
            return $option;
        });
    }
}
