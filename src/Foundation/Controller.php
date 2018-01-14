<?php

namespace WpPluginium\Framework\Foundation;

use WpPluginium\Framework\Loader;
use WpPluginium\Framework\Support\View;

use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Controller extends BaseController
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
