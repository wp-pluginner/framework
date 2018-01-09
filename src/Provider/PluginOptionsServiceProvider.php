<?php

namespace WpPluginner\Framework\Provider;

use WpPluginner\Framework\Foundation\ServiceProvider;
use WpPluginner\Framework\Support\PluginOptions;

class PluginOptionsServiceProvider extends ServiceProvider
{
    public function register(){
        $this->plugin->singleton('plugin.option', function ($plugin) {
            $option = new PluginOptions($plugin['config']->get('options',[]));
            return $option;
        });
    }
}
