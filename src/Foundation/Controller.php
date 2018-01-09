<?php

namespace WpPluginner\Framework\Foundation;

use WpPluginner\Framework\Loader;
use WpPluginner\Framework\Support\View;

use Illuminate\Support\Str;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Controller
{
    protected $plugin,$attributes;

    public function __construct($attributes = array(), $namespace = null )
    {
        $this->plugin = $this->getPluginInstance($namespace);
        $this->attributes = $attributes;
    }

    protected function getPluginInstance($namespace)
    {
        $namespace = is_null($namespace) ? (new \ReflectionClass(get_class($this)))->getNamespaceName() : $namespace;
        return Loader::getInstance($namespace);
    }

}
