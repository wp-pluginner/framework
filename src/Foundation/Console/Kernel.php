<?php

namespace WpPluginner\Framework\Foundation\Console;

use WpPluginner\Framework\Loader;

abstract class Kernel
{
    protected $plugin;
    protected $application;

    public function __construct( $namespace = null )
    {
        $this->plugin = $this->getPluginInstance($namespace);
        $this->application = new Application();
        $this->addFrameworkBaseCommands();

    }

    protected function getPluginInstance($namespace)
    {
        $namespace = is_null($namespace) ? (new \ReflectionClass(get_class($this)))->getNamespaceName() : $namespace;
        return Loader::getInstance($namespace);
    }

    protected function addFrameworkBaseCommands()
    {
        $classCommands = [
            'WpPluginner\Framework\Console\Command\ClearCacheView'
        ];
        foreach ($classCommands as $classCommand) {
            $this->application->add($this->plugin->make($classCommand));
        }
    }

    public function handle()
    {
        return $this->application->run();
    }



    protected function addDefaultCommands()
    {

    }
}
